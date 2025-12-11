// DBML for Kleening.id Revamp Project
// Blueprint v2.0
// Docs: https://dbml.dbdiagram.io/docs

//// ---------------- TAbles ----------------

Table users {
  id bigint [pk, increment, note: 'ID unik user']
  name varchar(255) [not null]
  phone_number varchar(255) [not null, unique]
  phone_verified_at timestamp [nullable]
  password varchar(255) [not null]
  role varchar(50) [not null, note: "'owner', 'co_owner', 'admin', 'staff'"]
  area_id bigint [nullable, note: 'Wajib diisi jika role = co_owner']
  remember_token varchar(100) [nullable]
  created_at timestamp
  updated_at timestamp
}

Table areas {
  id bigint [pk, increment, note: 'ID unik area']
  name varchar(255) [not null, unique]
  created_at timestamp
  updated_at timestamp
}

Table customers {
  id bigint [pk, increment, note: 'ID unik customer (PoC)']
  name varchar(255) [not null]
  phone_number varchar(255) [not null, unique]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [nullable]
}

Table addresses {
  id bigint [pk, increment, note: 'ID unik alamat']
  customer_id bigint [not null, note: 'Relasi ke pemilik alamat (PoC)']
  area_id bigint [nullable]
  label varchar(255) [not null, note: 'Contoh: Rumah Ibu, Kantor Serpong']
  contact_name varchar(255) [not null]
  contact_phone varchar(255) [not null]
  full_address text [not null]
  google_maps_link text [nullable]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [nullable]
}

Table staff {
  id bigint [pk, increment, note: 'ID unik staff']
  user_id bigint [nullable, note: 'Jika staff bisa login']
  area_id bigint [not null, note: 'Menentukan staff dari cabang mana']
  name varchar(255) [not null]
  phone_number varchar(255) [not null]
  is_active boolean [not null, default: true]
  created_at timestamp
  updated_at timestamp
}

Table service_categories {
  id bigint [pk, increment]
  name varchar(255) [not null, unique]
  created_at timestamp
  updated_at timestamp
}

Table services {
  id bigint [pk, increment]
  category_id bigint [not null]
  name varchar(255) [not null]
  price decimal(15, 2) [not null]
  description text [nullable]
  created_at timestamp
  updated_at timestamp
}

Table service_orders {
  id bigint [pk, increment, note: 'ID unik Service Order (SO)']
  so_number varchar(255) [not null, unique, note: 'Contoh: SO-2025-0001']
  customer_id bigint [not null, note: 'Siapa yang memesan']
  address_id bigint [not null, note: 'Di mana pekerjaan dilakukan']
  work_date date [not null]
  status varchar(50) [not null, note: "'draft', 'confirmed', 'invoiced', 'cancelled'"]
  work_notes text [nullable]
  staff_notes text [nullable, note: 'Catatan rahasia untuk staff']
  customer_signature_image longtext [nullable]
  work_proof_completed_at timestamp [nullable]
  created_by bigint [not null, note: 'User yang membuat SO']
  created_at timestamp
  updated_at timestamp
}

Table service_order_items {
  id bigint [pk, increment]
  service_order_id bigint [not null]
  service_id bigint [not null]
  quantity integer [not null]
  price decimal(15, 2) [not null, note: 'Harga saat itu (snapshot)']
  total decimal(15, 2) [not null]
}

Table service_order_staff {
  service_order_id bigint [pk]
  staff_id bigint [pk]
  signature_image longtext [nullable]
}

Table invoices {
  id bigint [pk, increment]
  service_order_id bigint [not null, unique]
  invoice_number varchar(255) [not null, unique, note: 'Contoh: INV/2025/09/001']
  issue_date date [not null]
  due_date date [not null]
  subtotal decimal(15, 2) [not null]
  discount decimal(15, 2) [not null, default: 0]
  discount_type varchar(255) [nullable]
  transport_fee decimal(15, 2) [not null, default: 0]
  grand_total decimal(15, 2) [not null]
  status varchar(50) [not null, default: 'new', note: "'new', 'sent', 'overdue', 'paid'"]
  signature text [nullable, note: 'Data Base64 atau path file gambar tanda tangan']
  created_at timestamp
  updated_at timestamp
}

Table payments {
  id bigint [pk, increment]
  reference_number varchar(255) [nullable]
  invoice_id bigint [not null]
  amount decimal(10, 2) [not null]
  payment_date date [not null]
  payment_method varchar(255) [not null]
  notes text [nullable]
  created_at timestamp
  updated_at timestamp
}

Table work_photos {
  id bigint [pk, increment]
  service_order_id bigint [not null]
  file_path varchar(255) [not null]
  type varchar(50) [not null, note: "'arrival', 'before', 'after'"]
  uploaded_by bigint [not null, note: 'Staff/user yang mengupload']
  created_at timestamp
}


//// ---------------- Relationships ----------------

// --- Relasi Master Data ---
Ref: users.area_id > areas.id
Ref: staff.user_id > users.id
Ref: staff.area_id > areas.id
Ref: addresses.customer_id > customers.id
Ref: addresses.area_id > areas.id
Ref: services.category_id > service_categories.id

// --- Relasi Transaksional ---
Ref: service_orders.customer_id > customers.id
Ref: service_orders.address_id > addresses.id
Ref: service_orders.created_by > users.id

Ref: service_order_items.service_order_id > service_orders.id
Ref: service_order_items.service_id > services.id

Ref: service_order_staff.service_order_id > service_orders.id
Ref: service_order_staff.staff_id > staff.id

Ref: work_photos.service_order_id > service_orders.id
Ref: work_photos.uploaded_by > users.id

Ref: payments.invoice_id > invoices.id

// Relasi One-to-One antara Service Order dan Invoice
Ref: invoices.service_order_id - service_orders.id
