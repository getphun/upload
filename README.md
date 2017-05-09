# upload

Adalah modul yang melayani file upload. Modul ini secara otomatis membuka satu route
ke `SITE:/comp/upload` yang hanya meneria request `POST` untuk memproses file upload.
Oleh karena itu, keberadaan gate `site` adalah harus agar modul ini bisa berjalan
dengan baik.

Setiap file yang di-upload harus menggunakan nama `file` dibarengi dengan kolom `form`
yang mendefinisikan nama form darimana file diupload dan nama `field` nya.

Ini adalah schema upload file untuk avatar user:

```
POST /comp/upload
    file = --BINARY--
    form = user-edit.avatar
```

Kolom `file` adalah file yang akan diproses, sementara kolom `form` menyimpan informasi
nama form dan nama field yang menyimpan informasi tentang upload. Untuk lebih jelasnya,
lihat konfigurasi form di bawah:

```php
<?php
// ./etc/config.php

return [
    'name' => 'Phun',
    ...,
    'form' => [
        'user-edit' => [
            'avatar' => [
                'type' => 'image',
                'label' => 'Avatar',
                'rules' => [
                    'media' => 'image/*'
                ]
            ]
        ]
    ]
];
```

Kolom yang mendefinisikan file upload harus memiliki rule `file`. Form rule `file`
adalah rule yang didefinisikan oleh modul ini.

Jika modul `db-mysql` ada, maka data file yang diupload akan disimpan di database
dengan tabel `media`. Jika module `user` ada, maka informasi user yang meng-upload
file juga akan disimpan.