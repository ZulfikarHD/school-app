export interface Student {
    id: number;
    nis: string;
    nisn: string;
    nik: string;
    nama_lengkap: string;
    nama_panggilan?: string;
    jenis_kelamin: 'L' | 'P';
    tempat_lahir: string;
    tanggal_lahir: string;
    agama: string;
    anak_ke: number;
    jumlah_saudara: number;
    status_keluarga: string;
    alamat: string;
    rt_rw?: string;
    kelurahan: string;
    kecamatan: string;
    kota: string;
    provinsi: string;
    kode_pos?: string;
    no_hp?: string;
    email?: string;
    foto?: string;
    kelas_id?: number;
    kelas?: {
        id: number;
        tingkat: number;
        nama: string;
        nama_lengkap: string;
        tahun_ajaran: string;
    };
    tahun_ajaran_masuk: string;
    tanggal_masuk: string;
    status: 'aktif' | 'mutasi' | 'do' | 'lulus';
    guardians?: Guardian[];
    primaryGuardian?: Guardian;
}

export interface Guardian {
    id: number;
    nik: string;
    nama_lengkap: string;
    hubungan: 'ayah' | 'ibu' | 'wali';
    pekerjaan: string;
    pendidikan: string;
    penghasilan: string;
    no_hp?: string;
    email?: string;
    alamat?: string;
    pivot?: { is_primary_contact: boolean };
}

export interface ClassHistory {
    id: number;
    kelas_id: number;
    tahun_ajaran: string;
    wali_kelas: string;
    created_at: string;
}

export interface StatusHistory {
    id: number;
    status_lama: string;
    status_baru: string;
    tanggal: string;
    alasan: string;
    changed_by: {
        id: number;
        name: string;
    };
}

