#!/bin/bash

# URL API yang akan di-hit
URL="https://shortener.its.ac.id/generate-qrbase64"

# Fungsi untuk memanggil API menggunakan curl
hit_api() {
    curl -X GET "$URL"
}

# Loop yang memanggil fungsi hit_api setiap 2 detik
while true; do
    hit_api
    sleep 2
done
