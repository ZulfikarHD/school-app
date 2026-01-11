import type { Student } from './student';
import type { User } from './index.d';

export interface StudentAttendance {
    id: number;
    student_id: number;
    class_id: number;
    tanggal: string;
    status: 'H' | 'I' | 'S' | 'A';
    keterangan?: string;
    recorded_by: number;
    recorded_at: string;
    created_at: string;
    updated_at: string;
    student?: Student & {
        nis: string;
        nama_lengkap: string;
    };
    class?: {
        id: number;
        nama_lengkap: string;
        tingkat: number;
        nama: string;
    };
    recorded_by?: User;
}

export interface LeaveRequest {
    id: number;
    student_id: number;
    jenis: 'IZIN' | 'SAKIT';
    tanggal_mulai: string;
    tanggal_selesai: string;
    alasan: string;
    attachment_path?: string;
    status: 'PENDING' | 'APPROVED' | 'REJECTED';
    submitted_by: number;
    reviewed_by?: number;
    reviewed_at?: string;
    rejection_reason?: string;
    created_at: string;
    updated_at: string;
    student?: Student;
    submitted_by_user?: User;
    reviewed_by_user?: User;
}

export interface TeacherAttendance {
    id: number;
    teacher_id: number;
    tanggal: string;
    clock_in?: string;
    clock_out?: string;
    clock_in_location?: string;
    clock_out_location?: string;
    status: 'HADIR' | 'TERLAMBAT' | 'IZIN' | 'SAKIT' | 'ALPHA';
    is_late: boolean;
    duration?: string;
    keterangan?: string;
    created_at: string;
    updated_at: string;
    teacher?: User;
}

export interface SubjectAttendance {
    id: number;
    teacher_id: number;
    class_id: number;
    subject_id: number;
    tanggal: string;
    pertemuan_ke: number;
    materi: string;
    keterangan?: string;
    created_at: string;
    updated_at: string;
}

export interface TeacherLeave {
    id: number;
    teacher_id: number;
    jenis: 'IZIN' | 'SAKIT' | 'CUTI';
    tanggal_mulai: string;
    tanggal_selesai: string;
    alasan: string;
    attachment_path?: string;
    status: 'PENDING' | 'APPROVED' | 'REJECTED';
    approved_by?: number;
    approved_at?: string;
    rejection_reason?: string;
    created_at: string;
    updated_at: string;
    teacher?: User;
    approved_by_user?: User;
}

export interface ClockStatus {
    is_clocked_in: boolean;
    is_clocked_out: boolean;
    clock_in?: string;
    clock_out?: string;
    status?: 'HADIR' | 'TERLAMBAT';
    is_late?: boolean;
    duration?: string;
}

export interface AttendanceSummary {
    total: number;
    hadir: number;
    izin: number;
    sakit: number;
    alpha: number;
}
