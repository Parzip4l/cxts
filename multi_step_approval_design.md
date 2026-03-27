# Multi-Step Approval Design

Dokumen ini menyiapkan arah implementasi multi-step approval yang **tetap kompatibel** dengan desain approval snapshot existing di project ini.

## Tujuan

Kita ingin menambah approval yang lebih enterprise-ready tanpa merusak flow yang sudah berjalan sekarang:

- rule approval tetap berasal dari taxonomy:
  - `Ticket Type`
  - `Ticket Category`
  - `Ticket Sub Category`
- snapshot approval yang sudah ada di `tickets` tetap dipakai
- ticket lama tetap valid
- flow single-step existing tetap jalan selama flow multi-step belum diaktifkan

## Kondisi Existing

Saat ini ticket sudah memiliki snapshot approval berikut:

- `requires_approval`
- `allow_direct_assignment`
- `approval_status`
- `expected_approver_id`
- `expected_approver_name_snapshot`
- `expected_approver_strategy`
- `expected_approver_role_code`
- `flow_policy_source`

Artinya fondasi snapshot sudah bagus. Multi-step approval sebaiknya **dibangun di atas snapshot ini**, bukan menggantinya total.

## Prinsip Kompatibilitas

### 1. Jangan hapus field approval existing di `tickets`

Field existing tetap dipakai sebagai:

- cache status approval utama
- fallback untuk ticket lama
- sumber cepat untuk list/dashboard/API

### 2. Tambah layer detail approval baru

Status detail multi-step tidak perlu ditaruh semua di `tickets`. Ticket cukup menyimpan:

- status agregat
- step aktif
- approver aktif

Detail per langkah disimpan di tabel baru.

### 3. Single-step dan multi-step harus bisa hidup berdampingan

Aturan aman:

- kalau taxonomy tidak punya approval flow multi-step
  - gunakan flow existing sekarang
- kalau taxonomy punya approval flow multi-step
  - generate approval steps ke ticket saat create

## Desain Data yang Direkomendasikan

### A. Approval Flow Definition

#### `approval_flows`

- `id`
- `name`
- `description`
- `is_active`

#### `approval_flow_steps`

- `id`
- `approval_flow_id`
- `step_order`
- `step_name`
- `approver_strategy`
- `approver_user_id` nullable
- `approver_role_code` nullable
- `use_requester_department_head` boolean default false
- `use_service_manager` boolean default false
- `is_parallel` boolean default false
- `is_required` boolean default true
- `is_active`

Catatan:
- `approver_strategy` sebaiknya tetap memakai vocabulary existing:
  - `specific_user`
  - `requester_department_head`
  - `service_manager`
  - `role_based`
  - `fallback`

### B. Flow Assignment Ke Taxonomy

#### Tambahan ke taxonomy table existing

- `approval_flow_id` nullable pada:
  - `ticket_categories`
  - `ticket_subcategories`
  - `ticket_detail_subcategories`

Rule precedence tetap sama:

1. `Ticket Sub Category`
2. `Ticket Category`
3. `Ticket Type`

### C. Ticket Approval Runtime

#### `ticket_approval_steps`

- `id`
- `ticket_id`
- `approval_flow_id`
- `step_order`
- `step_name`
- `approval_status`
- `approver_strategy_snapshot`
- `approver_user_id`
- `approver_name_snapshot`
- `approver_role_code`
- `is_parallel`
- `is_required`
- `acted_at` nullable
- `acted_by_id` nullable
- `decision_notes` nullable
- `created_at`
- `updated_at`

Status step:

- `pending`
- `approved`
- `rejected`
- `skipped`
- `cancelled`

#### Tambahan ringan ke `tickets`

- `approval_flow_id` nullable
- `approval_flow_name_snapshot` nullable
- `current_approval_step_order` nullable
- `current_approval_step_name_snapshot` nullable
- `approval_step_count` nullable

Field ini hanya untuk mempermudah list, dashboard, dan API.

## Flow Runtime Yang Direkomendasikan

### Saat Ticket Dibuat

1. Resolver policy tetap berjalan seperti sekarang
2. Jika `requires_approval = false`
   - flow existing tetap jalan
3. Jika `requires_approval = true`
   - cek apakah taxonomy punya `approval_flow_id`
   - jika tidak punya:
     - pakai single-step existing
   - jika punya:
     - generate `ticket_approval_steps`
     - resolve approver per step
     - set snapshot summary ke `tickets`

### Saat Approval Dijalankan

1. user hanya bisa approve step aktif yang menjadi haknya
2. jika step approved:
   - pindah ke next step
   - update summary di `tickets`
3. jika step rejected:
   - `tickets.approval_status = rejected`
   - ticket tidak bisa di-assign
4. jika semua step required approved:
   - `tickets.approval_status = approved`
   - gate assignment mengikuti `allow_direct_assignment`

## Cara Menjaga Kompatibilitas Existing

### Fase 1

Implement data structure baru, tapi:

- ticket tanpa `approval_flow_id` tetap pakai single-step
- controller approve/reject existing tetap dipakai

### Fase 2

Tambah service baru:

- `TicketApprovalFlowService`
- `TicketApprovalSnapshotService`

Tapi endpoint approve/reject existing tetap jadi facade di depan service baru.

### Fase 3

Update UI ticket detail:

- tampilkan current step
- tampilkan seluruh steps
- tampilkan siapa yang sudah approve / pending

### Fase 4

Tambahkan dashboard/report:

- approval aging by step
- rejection by step
- bottleneck approver

## Rule Implementasi Yang Paling Aman

### Rule 1

`tickets.approval_status` tetap jadi status agregat utama.

### Rule 2

`expected_approver_id` dan `expected_approver_role_code` tetap dipakai sebagai:

- approver aktif saat ini
- snapshot approver step yang sedang berjalan

Jadi field existing tidak mubazir.

### Rule 3

Approval history existing di `ticket_activities` tetap lanjut dicatat.

Selain itu, step detail juga dicatat di `ticket_approval_steps`.

## MVP Multi-Step Approval Setelah Demo

Versi MVP yang realistis:

1. dukung `serial approval` dulu
2. belum usah `parallel approval`
3. cukup 2-3 step maksimum
4. step strategy:
   - department head
   - service manager
   - role based
   - specific user

Contoh:

- `Access Request`
  - Step 1: Requester Department Head
  - Step 2: Operational Admin

- `New Install`
  - Step 1: Requester Department Head
  - Step 2: Service Manager

- `Major Change`
  - Step 1: Service Manager
  - Step 2: Supervisor
  - Step 3: Operational Admin

## Yang Belum Perlu Dipaksakan Sekarang

- CAB formal
- conditional branching kompleks
- amount-based approval
- dynamic rule builder sangat kompleks
- full BPMN/state-machine approval

Itu bisa datang setelah MVP multi-step terbukti stabil.

## Kesimpulan

Desain multi-step approval yang paling aman untuk project ini adalah:

- **tambahkan tabel flow + step runtime**
- **pertahankan snapshot existing di `tickets`**
- **pakai single-step existing sebagai fallback**
- **naik bertahap dari serial approval sederhana**

Dengan pendekatan ini, kita bisa naik ke enterprise governance tanpa rewrite total dan tanpa merusak ticket existing.
