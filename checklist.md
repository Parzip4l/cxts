# Checklist Pengembangan ITSM vs ITIL 4

Sumber mapping: `ITSM_Gap_Analysis_vs_ITIL4.xlsx`

Catatan: instruksi di workbook dipakai sebagai referensi analisis gap, bukan instruksi eksekusi langsung. Checklist ini menyesuaikan kondisi CXTS saat ini agar pengembangan tetap bertahap dan tidak over-engineer.

## Prinsip Implementasi

- [x] Mulai dari perluasan modul existing, terutama ticketing, SLA, asset, service catalog, approval, audit trail, dan dashboard.
- [x] Hindari membuat modul besar baru sebelum fondasi proses ITIL di ticket jelas.
- [x] Jaga kompatibilitas flow lama: ticket existing tetap berjalan sebagai `incident` secara default.
- [ ] Setiap modul baru harus punya manfaat operasional langsung, bukan hanya memenuhi istilah ITIL.

## Phase 1 - Must Have

### 1. Incident Management

- [x] Tambah klasifikasi proses dasar di ticket: `incident`, `service_request`, `change_request`.
- [x] Tambah kolom `tickets.process_type` dengan default `incident`.
- [x] Tambah helper label dan normalisasi process type di model `Ticket`.
- [x] Simpan `process_type` saat create ticket dari web, API, dan public portal.
- [x] Tampilkan process type di list ticket, detail ticket, form create, form edit, dan public ticket form.
- [x] Tambah filter process type di list ticket dan API ticket index.
- [x] Hubungkan context SLA ke `process_type`, tetap kompatibel dengan alias lama `ticket_type`.
- [x] Tambah cakupan test API untuk memastikan `process_type` tersimpan dan tampil di response.
- [x] Definisikan lifecycle Incident secara eksplisit: new, assigned, in progress, pending customer, resolved/completed, closed, reopened, cancelled.
- [x] Tambah field incident ringan: detection source, resolution code, major incident flag, affected users/count, service impact note.
- [x] Tambah rules prioritas berdasarkan impact + urgency, jika existing priority manual belum cukup.
- [x] Tambah report incident trend: top category, top service, top asset, breach, reopen, repeat incident.

### 2. Service Request Management + Service Catalog

- [x] Fondasi process type `service_request` sudah tersedia di ticket.
- [x] Service catalog existing sudah bisa dipakai sebagai konteks ticket.
- [x] Pisahkan UX Service Request dari Incident pada create ticket.
- [x] Tambah konfigurasi catalog item: visible to requester, default approval, default SLA, fulfillment team.
- [x] Tambah field/form per service request yang sederhana dan configurable.
- [x] Tambah request fulfillment state jika berbeda dari incident lifecycle.
- [x] Tambah report request fulfillment: volume, SLA, approval time, completion time.

### 3. Change Enablement

- [x] Fondasi process type `change_request` sudah tersedia di ticket.
- [x] Tentukan scope awal Change: standard change sederhana atau normal change dengan approval.
- [x] Tambah field minimal RFC: change reason, risk level, planned start/end, rollback plan, affected service/asset.
- [x] Reuse approval governance existing untuk approval change.
- [x] Tambah tampilan kalender/schedule change sederhana.
- [x] Tambah post implementation review ringan: result, issue, rollback used, notes.
- [x] Jangan membuat CAB workflow kompleks sebelum approval multi-step benar-benar dibutuhkan.

### 4. Configuration Management / CMDB / IT Asset Management

- [x] Asset, service, vendor, location, dan department owner sudah tersedia sebagai master data.
- [x] Ticket sudah bisa terhubung ke service, asset, dan asset location.
- [x] Tambah konsep Configuration Item bila asset biasa belum cukup.
- [x] Tambah relasi CI/service sederhana: parent, child, depends on, supports service.
- [x] Tambah impact view: service apa terdampak oleh asset/CI tertentu.
- [x] Tambah riwayat ticket per asset/service untuk analisis stabilitas.
- [x] Tambah validasi agar CMDB tidak tertukar dengan master configuration biasa.

### 5. Problem Management

- [x] Buat modul Problem minimal: problem number, title, description, status, owner, priority.
- [x] Hubungkan problem ke banyak incident/ticket.
- [x] Tambah RCA fields: symptom, root cause, workaround, permanent fix.
- [x] Tambah known error flag/status.
- [x] Tambah action item sederhana untuk solusi permanen.
- [x] Tambah trigger manual dari incident berulang ke problem.

### 6. Knowledge Management / KEDB

- [x] Buat knowledge article minimal: title, content, category, status, owner.
- [x] Tambah tipe artikel: troubleshooting, FAQ, workaround, known error.
- [x] Link knowledge ke incident/problem.
- [x] Tambah publish/draft sederhana.
- [x] Tambah pencarian knowledge dari detail ticket.
- [x] Tambah knowledge suggestion manual sebelum otomatisasi.

### 7. Monitoring & Event Management

- [x] Buat event intake minimal: source, severity, service/asset, message, occurred at.
- [x] Tambah mapping severity event ke impact/urgency incident.
- [x] Tambah fitur create incident manual dari event.
- [x] Tambah auto-create incident hanya untuk rule yang jelas dan terbatas.
- [x] Tambah deduplication sederhana untuk alert berulang.
- [x] Tambah dashboard event: open event, converted incident, top source.

### 8. Service Level Management Utuh

- [x] SLA policy, assignment, resolver, due date, warning, breach activity, dan dashboard dasar sudah tersedia.
- [x] SLA resolver sudah bisa memakai process type.
- [x] Tambah SLA event store agar perubahan state SLA audit-friendly.
- [x] Tambah OLA internal antar tim.
- [x] Tambah Underpinning Contract sederhana untuk vendor.
- [x] Tambah escalation rule berbasis SLA warning/breach.
- [x] Tambah review report berkala: SLA achievement, breach reason, trend per service.
- [x] Tambah snapshot/audit saat SLA policy berubah.

## Phase 2 - Should Have

### 1. Release & Deployment Management

- [ ] Tentukan apakah CXTS akan mengelola rilis aplikasi/internal infrastructure.
- [ ] Jika iya, buat release record minimal: version, scope, owner, planned date, status.
- [ ] Link release ke change request.
- [ ] Tambah deployment checklist sederhana.
- [ ] Tambah rollback note dan deployment result.

### 2. Availability & Capacity Management

- [ ] Mulai dari metric manual/import sederhana per service: uptime, downtime, capacity note.
- [ ] Tambah availability target per service jika sudah ada SLA layanan.
- [ ] Tambah capacity record sederhana: resource type, current usage, threshold, forecast note.
- [ ] Link downtime/capacity issue ke incident/problem.
- [ ] Tambah dashboard service health dasar.

### 3. Continual Improvement / CSI Register

- [ ] Buat CSI register minimal: title, source, owner, priority, status, expected benefit.
- [ ] Link CSI ke ticket, problem, SLA breach, audit finding, atau manual input.
- [ ] Tambah status sederhana: proposed, approved, in progress, done, rejected.
- [ ] Tambah review outcome dan evidence.
- [ ] Tambah report improvement by status/owner/benefit.

### 4. Supplier / Vendor Management

- [x] Vendor master data sudah tersedia.
- [ ] Tambah contract/UC record sederhana per vendor.
- [ ] Link vendor contract ke service/asset.
- [ ] Tambah vendor SLA target dan performance review.
- [ ] Tambah vendor escalation contact.
- [ ] Tambah report vendor breach dan open issue.

### 5. Service Desk / Self-Service Portal

- [x] Public portal sudah bisa submit dan track ticket.
- [x] Mobile execution sudah tersedia untuk engineer/inspection officer.
- [ ] Tambah portal requester yang lebih lengkap: my tickets, request catalog, knowledge search.
- [ ] Pisahkan tampilan submit Incident dan Service Request.
- [ ] Tambah tracking status yang ramah end-user.
- [ ] Tambah feedback/satisfaction sederhana setelah ticket closed.
- [ ] Tambah self-service knowledge sebelum submit ticket.

## Yang Sudah Dikerjakan Pada Phase 1 Saat Ini

- [x] Pondasi formal process type untuk ITIL di ticketing.
- [x] Database migration `process_type` dengan default `incident`.
- [x] Model helper untuk opsi, normalisasi, dan label process type.
- [x] Create ticket web/API/public sudah menyimpan process type.
- [x] Edit ticket web bisa koreksi process type.
- [x] List ticket dan API bisa filter berdasarkan process type.
- [x] Detail/list/form menampilkan label process type.
- [x] SLA context memakai process type tanpa mematahkan `ticket_type` lama.
- [x] Seeder ticket demo memakai process type.
- [x] Test API ticket diperbarui untuk process type.
- [x] Test yang dijalankan lulus: `TicketApiTest` dan `TicketLifecycleServiceTest`.
- [x] Incident lifecycle options ditambahkan di model/API dan ditampilkan pada detail ticket.
- [x] Incident triage fields ditambahkan ke database, model, request validation, API resource, create/edit form, list filter, dan detail ticket.
- [x] Priority matrix impact + urgency ditambahkan untuk create ticket saat priority tidak dipilih manual.
- [x] Incident trend dasar ditambahkan ke dashboard overview dan API dashboard.
- [x] Service catalog ditambah default Service Request: requestable, approval, SLA, fulfillment team, dan JSON schema field sederhana.
- [x] Create Service Request memakai default SLA/approval/fulfillment team dari service catalog.
- [x] Public portal hanya menampilkan service yang aktif dan requestable.
- [x] Create ticket internal menyembunyikan field khusus Incident saat process type bukan Incident.
- [x] Service Request lifecycle label ditambahkan sebagai fulfillment state ringan.
- [x] Request fulfillment report ditambahkan ke dashboard overview/API.
- [x] Field configurable dari `request_form_schema` dirender di form Service Request dan payload jawabannya disimpan di ticket.
- [x] Change Request ditambah field RFC minimal: reason, risk, planned window, rollback plan, affected scope.
- [x] Change Request memakai approval governance ticket category existing.
- [x] Dashboard overview ditambah Change Schedule sederhana berdasarkan planned start.
- [x] Post Implementation Review ringan ditambahkan lewat result dan notes.
- [x] Asset relationship sederhana ditambahkan untuk dependency/support antar asset.
- [x] Asset API menampilkan relationships dan impact view.
- [x] Asset/service list menampilkan ticket count sebagai indikator stabilitas.
- [x] Problem Management minimal ditambahkan dengan RCA, known error, action item, dan linked tickets.
- [x] Detail ticket punya trigger manual `Create Problem` untuk incident/problem berulang.
- [x] Knowledge Base/KEDB minimal ditambahkan dengan artikel draft/published dan tipe troubleshooting, FAQ, workaround, known error.
- [x] Knowledge article bisa dihubungkan ke ticket dan problem melalui web/API.
- [x] Detail ticket dan detail problem punya pencarian knowledge serta trigger manual `Create Article`.
- [x] Monitoring Event minimal ditambahkan dengan intake source/severity/service/asset/message/occurred at.
- [x] Event severity dipetakan ke impact/urgency incident untuk conversion.
- [x] Event bisa manual convert ke incident, ignore, dedup alert berulang, dan limited auto-create untuk high/critical.
- [x] Dashboard overview menampilkan event summary: open event, converted incident, severity, dan top source.
- [x] SLA event store ditambahkan untuk warning, breach, state change, dan escalation trigger.
- [x] SLA policy ditambah konfigurasi escalation sederhana untuk warning/breach.
- [x] Detail ticket dan SLA Performance menampilkan audit event SLA.
- [x] Perubahan SLA policy dicatat sebagai snapshot before/after di audit log khusus policy.
- [x] Service Commitments ditambahkan untuk OLA internal dan Underpinning Contract vendor.
- [x] OLA/UC bisa dikaitkan ke service, provider department/vendor, target response/resolution/availability, escalation contact, dan periode berlaku.
- [x] Change Enablement tetap memakai approval existing dan belum dibuat CAB workflow kompleks.
- [x] Asset bisa ditandai sebagai Configuration Item dengan CI type, lifecycle state, dan governance note.
- [x] Validasi CI mewajibkan CI type dan hubungan ke service atau CMDB relationship agar tidak menjadi master data biasa.

## Prioritas Berikutnya

1. Validasi kebutuhan CI tambahan agar CMDB tidak melebar dari asset/service yang sudah ada.
2. Hindari CAB workflow kompleks sampai approval multi-step benar-benar dibutuhkan.
3. Masuk Phase 2 hanya setelah flow utama Phase 1 stabil dipakai operasional.
