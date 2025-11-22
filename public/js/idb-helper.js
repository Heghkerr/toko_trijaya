// public/js/idb-helper.js

// Pastikan library 'idb' sudah dimuat (dari CDN)
if (typeof idb === 'undefined') {
    console.error("Library 'idb' tidak ditemukan. Pastikan sudah dimuat.");
}

const dbName = 'toko-trijaya-db';
const storeName = 'pending_transactions';

/**
 * Membuka database IndexedDB
 */
function openTrijayaDB() {
    return idb.openDB(dbName, 1, {
        upgrade(db) {
            // Buat 'tabel' (Object Store) untuk transaksi yang tertunda
            if (!db.objectStoreNames.contains(storeName)) {
                db.createObjectStore(storeName, {
                    keyPath: 'id', // Kunci unik
                    autoIncrement: true, // ID akan dibuat otomatis
                });
            }
        },
    });
}

/**
 * Menyimpan data transaksi ke 'Outbox' (IndexedDB)
 * @param {object} txData - Data transaksi yang akan disimpan
 */
async function saveTxToOutbox(txData) {
    try {
        const db = await openTrijayaDB();
        await db.add(storeName, txData);
        console.log('Transaksi berhasil disimpan ke Outbox (IndexedDB).');

        // Beri tahu pengguna
        // (Anda bisa mengganti 'alert' ini dengan notifikasi yang lebih cantik)
        alert('Koneksi internet mati. Transaksi Anda telah disimpan di HP dan akan dikirim otomatis saat kembali online.');

    } catch (error) {
        console.error('Gagal menyimpan transaksi ke IndexedDB:', error);
    }
}

/**
 * (UNTUK BAGIAN 2 NANTI)
 * Mengambil semua transaksi dari 'Outbox'
 */
async function getAllPendingTxs() {
    const db = await openTrijayaDB();
    return await db.getAll(storeName);
}

/**
 * (UNTUK BAGIAN 2 NANTI)
 * Menghapus transaksi dari 'Outbox' setelah berhasil dikirim
 * @param {number} id - ID dari transaksi yang akan dihapus
 */
async function deletePendingTx(id) {
    const db = await openTrijayaDB();
    await db.delete(storeName, id);
    console.log(`Transaksi ${id} berhasil dihapus dari Outbox.`);
}
