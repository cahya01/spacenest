#include <time.h>
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <UniversalTelegramBot.h>
#include <ArduinoJson.h>
#include <ESP32Servo.h>

Servo myServo;

// ============================
// Konfigurasi WiFi
// ============================
const char* ssid = "vabell";
const char* password = "vabell123";

const char* ntpServer = "pool.ntp.org";

const long gmtOffset_sec = 7 * 3600;      // WIB
const int daylightOffset_sec = 0;

// ============================
// Jadwal Pakan
// ============================

int jamPakan1 = 8;
int menitPakan1 = 0;

int jamPakan2 = 17;
int menitPakan2 = 0;

bool sudahPakan1 = false;
bool sudahPakan2 = false;
bool resetHariIni = false;

// ============================
// Status sinkronisasi waktu (NON-BLOCKING)
// ============================
bool waktuSudahSinkron = false;
unsigned long lastNtpAttempt = 0;
const unsigned long NTP_RETRY_INTERVAL = 10000; // coba ulang tiap 10 detik

// ============================
// Riwayat Pakan
// ============================

String waktuPakanTerakhir = "-";
String sumberPakanTerakhir = "-";

// ============================
// Mode Input Jadwal
// ============================

bool menungguJamPagi = false;
bool menungguJamSore = false;

// ============================
// Telegram
// ============================
const char* BOT_TOKEN = "8907579118:AAF244VmKcaJmUoIyZRn4V4tyMDV5r1hRHI";

const String AUTHORIZED_CHAT_ID = "5544269444";

WiFiClientSecure client;
UniversalTelegramBot bot(BOT_TOKEN, client);

// ============================
// Telegram Polling
// ============================
unsigned long lastTimeBotRan = 0;

const unsigned long BOT_INTERVAL = 1000;

// ============================
// Konfigurasi Servo
// ============================
const int PIN_SERVO = 18;
const int POSISI_TUTUP = 0;
const int POSISI_BUKA = 70;
const int DURASI_BUKA = 500;

// ============================
// Setup
// ============================

void koneksiWiFi();
void bacaPerintah();
void beriPakan(String sumber);
void tampilkanHelp();
void tampilkanStatus();
void cekTelegram();
void handleNewMessages(int jumlahPesan);
void tampilkanWaktu();
void cekJadwalPakan();
void cekKoneksiWiFi();
void cobaSinkronWaktu();
void kirimMenuTelegram(String chat_id);

void setup() {

  Serial.begin(115200);

  myServo.setPeriodHertz(50);
  myServo.attach(PIN_SERVO, 500, 2400);
  myServo.write(POSISI_TUTUP);

  Serial.println();
  Serial.println("================================");
  Serial.println("   Automatic Fish Feeder");
  Serial.println("================================");
  Serial.println("Perintah yang tersedia:");
  Serial.println("feed");
  Serial.println("status");
  Serial.println("time");
  Serial.println("help");
  Serial.println("================================");

  koneksiWiFi();
  client.setInsecure();

  configTime(
    gmtOffset_sec,
    daylightOffset_sec,
    ntpServer
  );

  Serial.println("Sinkronisasi waktu...");

  // ============================
  // PERBAIKAN #1:
  // Loop sinkronisasi waktu diberi TIMEOUT.
  // Sebelumnya "while (!getLocalTime(&timeinfo))" bisa
  // hang SELAMANYA kalau NTP gagal diakses, sehingga
  // setup() tidak pernah selesai dan loop() (termasuk bot
  // Telegram & pembacaan Serial) tidak pernah berjalan.
  // ============================
  struct tm timeinfo;
  unsigned long ntpStart = millis();
  const unsigned long NTP_TIMEOUT_MS = 15000; // maksimal 15 detik coba di setup

  while (!getLocalTime(&timeinfo) &&
         millis() - ntpStart < NTP_TIMEOUT_MS) {

    Serial.print(".");
    delay(500);

  }

  if (getLocalTime(&timeinfo)) {

    waktuSudahSinkron = true;
    Serial.println();
    Serial.println("Waktu berhasil disinkronkan.");
    tampilkanWaktu();

  } else {

    Serial.println();
    Serial.println("Gagal sinkron waktu (timeout). Akan dicoba ulang di background.");
    Serial.println("Alat tetap lanjut jalan (Telegram & Serial tetap aktif).");

  }

  sudahPakan1 = false;
  sudahPakan2 = false;
  resetHariIni = false;

  Serial.println("Telegram siap digunakan.");
  bot.sendMessage(
    AUTHORIZED_CHAT_ID,
    "✅ Automatic Fish Feeder berhasil online.",
    ""
);

kirimMenuTelegram(AUTHORIZED_CHAT_ID);
}

// ============================
// Loop
// ============================
void loop() {

  cekKoneksiWiFi();

  cobaSinkronWaktu();

  bacaPerintah();

  cekTelegram();

  cekJadwalPakan();

}

// ============================
// Koneksi WiFi
// ============================
void koneksiWiFi() {

  Serial.println();
  Serial.print("Menghubungkan ke WiFi : ");
  Serial.println(ssid);

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {

    delay(500);
    Serial.print(".");

    static int counter = 0;
    counter++;

    if (counter % 20 == 0) {
        Serial.println();
        Serial.println("Masih mencoba menghubungkan WiFi...");
    }

}

  Serial.println();
  Serial.println();
  Serial.println("================================");
  Serial.println("WiFi Berhasil Terhubung");
  Serial.print("IP Address : ");
  Serial.println(WiFi.localIP());
  Serial.println("================================");
  Serial.println();

}

void cekKoneksiWiFi() {

  static unsigned long lastReconnectAttempt = 0;

  if (WiFi.status() == WL_CONNECTED) {
    return;
  }

  if (millis() - lastReconnectAttempt < 10000) {
    return;
  }

  lastReconnectAttempt = millis();

  Serial.println();
  Serial.println("WiFi terputus.");
  Serial.println("Mencoba reconnect...");

  WiFi.disconnect();
  WiFi.begin(ssid, password);

  unsigned long start = millis();

  while (WiFi.status() != WL_CONNECTED &&
         millis() - start < 10000) {

    delay(500);
    Serial.print(".");

  }

  if (WiFi.status() == WL_CONNECTED) {

    Serial.println();
    Serial.println("Reconnect berhasil.");

    configTime(
      gmtOffset_sec,
      daylightOffset_sec,
      ntpServer
    );

    client.setInsecure();

    bot.sendMessage(
      AUTHORIZED_CHAT_ID,
      "📶 WiFi berhasil reconnect.",
      ""
    );

  } else {

    Serial.println();
    Serial.println("Reconnect gagal.");

  }

}

// ============================
// PERBAIKAN #1 (lanjutan):
// Fungsi non-blocking untuk mencoba sinkron waktu ulang
// di background tanpa menghentikan loop() utama.
// ============================
void cobaSinkronWaktu() {

  if (waktuSudahSinkron) {
    return;
  }

  if (millis() - lastNtpAttempt < NTP_RETRY_INTERVAL) {
    return;
  }

  lastNtpAttempt = millis();

  struct tm timeinfo;

  if (getLocalTime(&timeinfo)) {

    waktuSudahSinkron = true;
    Serial.println();
    Serial.println("Waktu berhasil disinkronkan (background).");
    tampilkanWaktu();

  }

}

// ============================
// Membaca Perintah
// ============================
void bacaPerintah() {

  if (Serial.available()) {

    String perintah = Serial.readStringUntil('\n');
    perintah.trim();
    perintah.toLowerCase();

    if (perintah == "feed") {

      beriPakan("Serial");

    }
    else if (perintah == "status") {

      tampilkanStatus();

    }
    else if (perintah == "time") {

      tampilkanWaktu();

    }
    else if (perintah == "help") {

      tampilkanHelp();

    }
    else {

      Serial.println();
      Serial.println("Perintah tidak dikenal.");
      Serial.println("Ketik help untuk bantuan.");
      Serial.println();

    }
  }

}

// ============================
// Memberikan Pakan
// ============================
void beriPakan(String sumber) {

  Serial.println();
  Serial.println("===== DEBUG =====");
  Serial.println("Masuk fungsi beriPakan()");
  Serial.print("Sumber : ");
  Serial.println(sumber);

  Serial.println("Servo buka...");
  myServo.write(POSISI_BUKA);

  delay(DURASI_BUKA);

  Serial.println("Servo tutup...");
  myServo.write(POSISI_TUTUP);

  delay(300);


  

  struct tm timeinfo;

  if (getLocalTime(&timeinfo)) {

    char buffer[30];

    strftime(
      buffer,
      sizeof(buffer),
      "%d/%m/%Y %H:%M:%S",
      &timeinfo
    );

    waktuPakanTerakhir = buffer;
    sumberPakanTerakhir = sumber;

  } else {

    waktuPakanTerakhir = "waktu belum sinkron";
    sumberPakanTerakhir = sumber;

  }

  Serial.println(">>> Pakan berhasil diberikan.");
  Serial.println();
}
// ============================
// Help
// ============================
void tampilkanHelp() {

  Serial.println("===== DAFTAR PERINTAH =====");
  Serial.println("feed   -> Memberikan pakan");
  Serial.println("status -> Melihat status alat");
  Serial.println("time   -> Menampilkan waktu");
  Serial.println("help   -> Menampilkan bantuan");
  Serial.println("===========================");

}

// ============================
// Status
// ============================
void tampilkanStatus() {

  Serial.println();
  Serial.println("===== STATUS ALAT =====");

  Serial.println("Servo      : READY");
  Serial.print("Pakan Terakhir : ");
  Serial.println(waktuPakanTerakhir);

  Serial.print("Sumber         : ");
  Serial.println(sumberPakanTerakhir);

  if (WiFi.status() == WL_CONNECTED) {

    Serial.println("WiFi       : CONNECTED");
    Serial.print("IP Address : ");
    Serial.println(WiFi.localIP());

  } else {

    Serial.println("WiFi       : DISCONNECTED");

  }

  if (WiFi.status() == WL_CONNECTED) {

    Serial.println("Telegram   : CONNECTED");

  } else {

    Serial.println("Telegram   : DISCONNECTED");

  }

  Serial.print("Waktu      : ");
  Serial.println(waktuSudahSinkron ? "SYNCED" : "BELUM SYNC");

  Serial.println();

  Serial.println("Jadwal :");

  Serial.print("Pagi : ");

  if (jamPakan1 < 10) Serial.print("0");
  Serial.print(jamPakan1);

  Serial.print(":");

  if (menitPakan1 < 10) Serial.print("0");
  Serial.println(menitPakan1);

  Serial.print("Sore : ");

  if (jamPakan2 < 10) Serial.print("0");
  Serial.print(jamPakan2);

  Serial.print(":");

  if (menitPakan2 < 10) Serial.print("0");
  Serial.println(menitPakan2);

  Serial.println("=======================");
  Serial.println();

}
// ============================
// Tampilkan Waktu
// ============================
void tampilkanWaktu() {

  struct tm timeinfo;

  if (!getLocalTime(&timeinfo)) {

    Serial.println("Gagal mendapatkan waktu.");
    return;

  }

  Serial.println();
  Serial.println("===== WAKTU SAAT INI =====");

  Serial.print("Tanggal : ");
  Serial.print(timeinfo.tm_mday);
  Serial.print("/");
  Serial.print(timeinfo.tm_mon + 1);
  Serial.print("/");
  Serial.println(timeinfo.tm_year + 1900);

  Serial.print("Jam     : ");

  if (timeinfo.tm_hour < 10) Serial.print("0");
  Serial.print(timeinfo.tm_hour);
  Serial.print(":");

  if (timeinfo.tm_min < 10) Serial.print("0");
  Serial.print(timeinfo.tm_min);
  Serial.print(":");

  if (timeinfo.tm_sec < 10) Serial.print("0");
  Serial.println(timeinfo.tm_sec);

  Serial.println("==========================");
  Serial.println();

}

void cekJadwalPakan() {

  if (!waktuSudahSinkron) {
    return; // jangan cek jadwal sebelum waktu valid, mencegah trigger salah di epoch 1970
  }

  struct tm timeinfo;

  if (!getLocalTime(&timeinfo)) {
    return;
  }

  int jam = timeinfo.tm_hour;
  int menit = timeinfo.tm_min;

  // ============================
  // Reset setiap hari (sekali)
  // ============================
  if (jam == 0 && menit == 0 && !resetHariIni) {

    sudahPakan1 = false;
    sudahPakan2 = false;
    resetHariIni = true;

    Serial.println();
    Serial.println("===== RESET JADWAL =====");
    Serial.println("Scheduler siap untuk hari baru.");
    Serial.println("========================");

  }

  // Setelah lewat jam 00:00,
  // reset flag resetHariIni
  if (jam == 0 && menit > 0) {

    resetHariIni = false;

  }

  // ============================
  // Jadwal Pertama
  // ============================
  if (jam == jamPakan1 &&
      menit == menitPakan1 &&
      !sudahPakan1) {

    Serial.println();
    Serial.println("===== SCHEDULER =====");
    Serial.println("Jadwal pagi tercapai.");

    beriPakan("Scheduler Pagi");

    String pesan = "🐟 Jadwal pagi dijalankan\n";
    pesan += waktuPakanTerakhir;

    bot.sendMessage(
        AUTHORIZED_CHAT_ID,
        pesan,
        ""
    );

    sudahPakan1 = true;

  }

  // ============================
  // Jadwal Kedua
  // ============================
  if (jam == jamPakan2 &&
      menit == menitPakan2 &&
      !sudahPakan2) {

    Serial.println();
    Serial.println("===== SCHEDULER =====");
    Serial.println("Jadwal sore tercapai.");

    beriPakan("Scheduler Sore");
    String pesan = "🐟 Jadwal sore dijalankan\n";
    pesan += waktuPakanTerakhir;

    bot.sendMessage(
        AUTHORIZED_CHAT_ID,
        pesan,
        ""
    );

    sudahPakan2 = true;

  }

}

void cekTelegram() {

  if (millis() - lastTimeBotRan >= BOT_INTERVAL) {

    int jumlahPesan = bot.getUpdates(bot.last_message_received + 1);

    // ============================
    // PERBAIKAN #2:
    // Sebelumnya "while (jumlahPesan)" — kalau getUpdates()
    // mengembalikan nilai negatif (error jaringan/TLS ke
    // server Telegram), kondisi ini tetap dianggap "true"
    // sehingga program bisa terjebak infinite loop di sini
    // dan seluruh alat (feed/status/scheduler) jadi freeze
    // tanpa respon apapun. Sekarang dicek eksplisit > 0.
    // ============================
    while (jumlahPesan > 0) {

      handleNewMessages(jumlahPesan);

      jumlahPesan = bot.getUpdates(bot.last_message_received + 1);

    }

    if (jumlahPesan < 0) {
      Serial.println("Peringatan: gagal mengambil update Telegram (cek koneksi/token).");
    }

    lastTimeBotRan = millis();

  }

}

void kirimMenuTelegram(String chat_id) {

  String keyboard =
      "[[\"🐟 Feed\",\"📊 Status\"],"
      "[\"🕒 Time\",\"⚙️ Jadwal\"],"
      "[\"❓ Help\"]]";

  bot.sendMessageWithReplyKeyboard(
      chat_id,
      "Silakan pilih menu:",
      "",
      keyboard,
      true
  );

}

void handleNewMessages(int jumlahPesan) {

  for (int i = 0; i < jumlahPesan; i++) {

    String chat_id = String(bot.messages[i].chat_id);

    if (chat_id != AUTHORIZED_CHAT_ID) {
      bot.sendMessage(chat_id, "Anda tidak memiliki akses.", "");
      continue;
    }

    String text = bot.messages[i].text;
    text.trim();

    String fromName = bot.messages[i].from_name;
    Serial.println();
    Serial.println("==============================");
    Serial.print("Pesan dari : ");
    Serial.println(fromName);

    Serial.print("Chat ID    : ");
    Serial.println(chat_id);

    Serial.print("Isi Pesan  : ");
    Serial.println(text);


    if (text == "/feed" || text == "🐟 Feed") {

      beriPakan("Telegram");

      String pesan = "🐟 Pakan berhasil diberikan.\n";
      pesan += waktuPakanTerakhir;

      bot.sendMessage(chat_id, pesan, "");

    }
    else if (text == "/status" || text == "📊 Status") {

      String status = "";

      status += "===== STATUS ALAT =====\n";
      status += "Servo : READY\n";
      status += "Pakan Terakhir : ";
      status += waktuPakanTerakhir;
      status += "\n";

      status += "Sumber : ";
      status += sumberPakanTerakhir;
      status += "\n";

      if (WiFi.status() == WL_CONNECTED) {

        status += "WiFi : CONNECTED\n";
        status += "IP : ";
        status += WiFi.localIP().toString();
        status += "\nTelegram : CONNECTED";

        status += "\n\nJadwal :\n";

        status += "Pagi : ";

        if (jamPakan1 < 10) status += "0";
        status += String(jamPakan1);
        status += ":";

        if (menitPakan1 < 10) status += "0";
        status += String(menitPakan1);

        status += "\n";

        status += "Sore : ";

        if (jamPakan2 < 10) status += "0";
        status += String(jamPakan2);
        status += ":";

        if (menitPakan2 < 10) status += "0";
        status += String(menitPakan2);
      } else {

        status += "WiFi : BELUM TERHUBUNG";
        status += "\nTelegram : DISCONNECTED";

      }

      bot.sendMessage(chat_id, status, "");

    }
    else if (text == "/time" || text == "🕒 Time") {

      struct tm timeinfo;

      if (getLocalTime(&timeinfo)) {

        char waktu[30];

        strftime(
          waktu,
          sizeof(waktu),
          "%d/%m/%Y\n%H:%M:%S",
          &timeinfo
        );

        String pesan = "🕒 Waktu ESP32\n";
        pesan += waktu;

        bot.sendMessage(chat_id, pesan, "");

      }
      else {

        bot.sendMessage(
          chat_id,
          "❌ Gagal mendapatkan waktu.",
          ""
        );

      }

    }
    else if (text == "🌅 Ubah Jadwal Pagi") {

    menungguJamPagi = true;
    menungguJamSore = false;

    bot.sendMessage(
        chat_id,
        "🌅 Ubah Jadwal Pagi\n\nSilakan masukkan jam baru.\n\nFormat:\nHH:MM\n\nContoh:\n08:30",
        ""
    );

}
else if (text == "🌙 Ubah Jadwal Sore") {

    menungguJamSore = true;
    menungguJamPagi = false;

    bot.sendMessage(
        chat_id,
        "🌙 Ubah Jadwal Sore\n\nSilakan masukkan jam baru.\n\nFormat:\nHH:MM\n\nContoh:\n17:30",
        ""
    );

}

else if (menungguJamPagi) {

    if (text.length() == 5 &&
        text.charAt(2) == ':' &&
        isdigit(text.charAt(0)) &&
        isdigit(text.charAt(1)) &&
        isdigit(text.charAt(3)) &&
        isdigit(text.charAt(4))) {

        int jam = text.substring(0, 2).toInt();
        int menit = text.substring(3, 5).toInt();

        if (jam >= 0 && jam <= 23 &&
            menit >= 0 && menit <= 59) {

            jamPakan1 = jam;
            menitPakan1 = menit;

            sudahPakan1 = false;
            menungguJamPagi = false;

            String balasan = "✅ Jadwal pagi berhasil diubah menjadi ";

if (jam < 10) balasan += "0";
balasan += String(jam);
balasan += ":";

if (menit < 10) balasan += "0";
balasan += String(menit);

bot.sendMessage(chat_id, balasan, "");

delay(500);

String keyboard =
"[[\"🌅 Ubah Jadwal Pagi\"],"
 "[\"🌙 Ubah Jadwal Sore\"],"
 "[\"⬅️ Kembali\"]]";


bot.sendMessageWithReplyKeyboard(
    chat_id,
    "Silakan pilih menu jadwal:",
    "",
    keyboard,
    true
);

kirimMenuTelegram(chat_id);
        }
        else {

            bot.sendMessage(
                chat_id,
                "Jam tidak valid.",
                ""
            );

        }

    }
    else {

        bot.sendMessage(
            chat_id,
            "Format salah.\nContoh:\n08:30",
            ""
        );

    }

}

else if (menungguJamSore) {

    if (text.length() == 5 &&
        text.charAt(2) == ':' &&
        isdigit(text.charAt(0)) &&
        isdigit(text.charAt(1)) &&
        isdigit(text.charAt(3)) &&
        isdigit(text.charAt(4))) {

        int jam = text.substring(0, 2).toInt();
        int menit = text.substring(3, 5).toInt();

        if (jam >= 0 && jam <= 23 &&
            menit >= 0 && menit <= 59) {

            jamPakan2 = jam;
            menitPakan2 = menit;

            sudahPakan2 = false;
            menungguJamSore = false;

            String balasan = "✅ Jadwal sore berhasil diubah menjadi ";

            if (jam < 10) balasan += "0";
            balasan += String(jam);
            balasan += ":";

            if (menit < 10) balasan += "0";
            balasan += String(menit);

            bot.sendMessage(chat_id, balasan, "");

            delay(500);

            String keyboard =
            "[[\"🌅 Ubah Jadwal Pagi\"],"
             "[\"🌙 Ubah Jadwal Sore\"],"
             "[\"⬅️ Kembali\"]]";

            bot.sendMessageWithReplyKeyboard(
                chat_id,
                "Silakan pilih menu jadwal:",
                "",
                keyboard,
                true
            );

        }
        else {

            bot.sendMessage(
                chat_id,
                "Jam tidak valid.",
                ""
            );

        }

    }
    else {

        bot.sendMessage(
            chat_id,
            "Format salah.\nContoh:\n17:30",
            ""
        );

    }

}

    else if (text.startsWith("/setpagi ")) {

      String jamBaru = text.substring(9);
      jamBaru.trim();

      if (jamBaru.length() != 5 ||
          jamBaru.charAt(2) != ':' ||
          !isdigit(jamBaru.charAt(0)) ||
          !isdigit(jamBaru.charAt(1)) ||
          !isdigit(jamBaru.charAt(3)) ||
          !isdigit(jamBaru.charAt(4))) {

        bot.sendMessage(
            chat_id,
            "Format salah.\nContoh:\n/setpagi 08:30",
            ""
        );

        continue;
      }

      int titik = jamBaru.indexOf(':');

      int jam = jamBaru.substring(0, titik).toInt();
      int menit = jamBaru.substring(titik + 1).toInt();

      if (jam < 0 || jam > 23 || menit < 0 || menit > 59) {

        bot.sendMessage(
            chat_id,
            "Jam tidak valid.",
            ""
        );

      } else {

        jamPakan1 = jam;
        menitPakan1 = menit;

        sudahPakan1 = false;

        struct tm timeinfo;

        if (getLocalTime(&timeinfo)) {

          if (timeinfo.tm_hour == jam &&
              timeinfo.tm_min == menit) {

            sudahPakan1 = true;

          }

        }

        // Log ke Serial Monitor
        Serial.println();
        Serial.println("===== UPDATE JADWAL =====");
        Serial.print("Jadwal Pagi -> ");

        if (jam < 10) Serial.print("0");
        Serial.print(jam);
        Serial.print(":");

        if (menit < 10) Serial.print("0");
        Serial.println(menit);

        Serial.println("=========================");

        String balasan = "✅ Jadwal pagi diubah menjadi ";

        if (jam < 10) balasan += "0";
        balasan += String(jam);
        balasan += ":";

        if (menit < 10) balasan += "0";
        balasan += String(menit);

        bot.sendMessage(chat_id, balasan, "");

      }

    }
    else if (text.startsWith("/setsore ")) {

      String jamBaru = text.substring(9);
      jamBaru.trim();

      if (jamBaru.length() != 5 ||
          jamBaru.charAt(2) != ':' ||
          !isdigit(jamBaru.charAt(0)) ||
          !isdigit(jamBaru.charAt(1)) ||
          !isdigit(jamBaru.charAt(3)) ||
          !isdigit(jamBaru.charAt(4))) {

        bot.sendMessage(
            chat_id,
            "Format salah.\nContoh:\n/setsore 17:30",
            ""
        );

        continue;
      }

      int titik = jamBaru.indexOf(':');

      int jam = jamBaru.substring(0, titik).toInt();
      int menit = jamBaru.substring(titik + 1).toInt();

      if (jam < 0 || jam > 23 || menit < 0 || menit > 59) {

        bot.sendMessage(
            chat_id,
            "Jam tidak valid.",
            ""
        );

      } else {

        jamPakan2 = jam;
        menitPakan2 = menit;

        sudahPakan2 = false;

        struct tm timeinfo;

        if (getLocalTime(&timeinfo)) {

          if (timeinfo.tm_hour == jam &&
              timeinfo.tm_min == menit) {

            sudahPakan2 = true;

          }

        }

        // Log ke Serial Monitor
        Serial.println();
        Serial.println("===== UPDATE JADWAL =====");
        Serial.print("Jadwal Sore -> ");

        if (jam < 10) Serial.print("0");
        Serial.print(jam);
        Serial.print(":");

        if (menit < 10) Serial.print("0");
        Serial.println(menit);

        Serial.println("=========================");

        String balasan = "✅ Jadwal sore diubah menjadi ";

        if (jam < 10) balasan += "0";
        balasan += String(jam);
        balasan += ":";

        if (menit < 10) balasan += "0";
        balasan += String(menit);

        bot.sendMessage(chat_id, balasan, "");
        delay(500);

String keyboard =
"[[\"🌅 Ubah Jadwal Pagi\"],"
 "[\"🌙 Ubah Jadwal Sore\"],"
 "[\"⬅️ Kembali\"]]";


bot.sendMessageWithReplyKeyboard(
    chat_id,
    "Silakan pilih menu jadwal:",
    "",
    keyboard,
    true
);

      }

    }

    else if (text == "⚙️ Jadwal") {

    String pesan = "";

    pesan += "📅 Jadwal Saat Ini\n\n";

    pesan += "Pagi : ";

    if (jamPakan1 < 10) pesan += "0";
    pesan += String(jamPakan1);
    pesan += ":";

    if (menitPakan1 < 10) pesan += "0";
    pesan += String(menitPakan1);

    pesan += "\n";

    pesan += "Sore : ";

    if (jamPakan2 < 10) pesan += "0";
    pesan += String(jamPakan2);
    pesan += ":";

    if (menitPakan2 < 10) pesan += "0";
    pesan += String(menitPakan2);

    pesan += "\n\n";

   pesan += "Pilih menu di bawah untuk mengubah jadwal.";

String keyboard =
"[[\"🌅 Ubah Jadwal Pagi\"],"
 "[\"🌙 Ubah Jadwal Sore\"],"
 "[\"⬅️ Kembali\"]]";

bot.sendMessageWithReplyKeyboard(
    chat_id,
    pesan,
    "",
    keyboard,
    true
);
}
else if (text == "⬅️ Kembali") {

    kirimMenuTelegram(chat_id);

}
    else if (text == "/help" || text == "❓ Help") {

    kirimMenuTelegram(chat_id);

}

    else {

      bot.sendMessage(
          chat_id,
          "Perintah tidak dikenal.\nGunakan /help",
          ""
      );

    }

    Serial.println("==============================");

  }

}
