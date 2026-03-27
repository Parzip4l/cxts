# Engineering Operations Mobile

Mobile app Flutter untuk consume API backend Laravel pada domain:

- Engineer ticket execution (`/api/v1/engineer/*`)
- Inspection execution (`/api/v1/inspection/*`)
- Auth & profile (`/api/v1/auth/*`)

## Fitur

- Login API token
- Role-based menu:
  - `engineer`: Dashboard, Tasks, Inspections, Schedule (Calendar), Profile
  - `inspection_officer`: My Inspections, Results, Profile
- Header modern:
  - placeholder logo
  - tombol notifikasi
  - avatar profile dropdown (Profile / Logout)
- Dashboard engineer:
  - slicer periode 7/14/30 hari
  - KPI assigned/completed/effectiveness/SLA resolution
  - progress compliance SLA response & resolution
  - rata-rata response/resolution time
  - recent assigned tickets
- Task lifecycle:
  - start, pause, resume, complete
  - tambah worklog
- Inspection lifecycle:
  - list inspeksi dalam card view
  - update item checklist
  - upload evidence
  - submit final result normal/abnormal
  - abnormal wajib file pendukung
- Engineer juga bisa jalankan flow inspeksi (sesuai permission API terbaru)
- Notification center:
  - feed notifikasi ticket + inspeksi
  - token FCM diregister otomatis saat login di device
- Lihat linked ticket dari inspeksi abnormal
- Edit profile via API

## Jalankan

```bash
cd mobile_engineering_app
flutter pub get
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1
```

Catatan URL:

- Android emulator: `http://10.0.2.2:8000/api/v1`
- iOS simulator (Mac): `http://127.0.0.1:8000/api/v1`

## API tambahan untuk mobile inspeksi

Agar form create inspection usable di mobile tanpa input ID manual, backend menyediakan endpoint read-only untuk role `inspection_officer` dan `engineer`:

- `GET /api/v1/inspection/templates`
- `GET /api/v1/inspection/assets`
- `GET /api/v1/inspection/asset-locations`

## API notifikasi Firebase-ready

- `GET /api/v1/mobile/notifications`
- `POST /api/v1/mobile/notifications/device-token`
- `DELETE /api/v1/mobile/notifications/device-token`
- `GET /api/v1/mobile/notifications/firebase-config`

Catatan: endpoint register/delete token digunakan internal app secara otomatis, tidak ada input token manual dari UI.
