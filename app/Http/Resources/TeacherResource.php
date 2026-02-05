<?php

namespace App\Http\Resources;

use App\Enums\StatusKepegawaian;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    /**
     * Transform the teacher resource into an array untuk API dan Inertia responses
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,

            // Identitas
            'nip' => $this->nip,
            'nik' => $this->nik,
            'formatted_nip' => $this->formatted_nip,

            // Biodata
            'nama_lengkap' => $this->nama_lengkap,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir?->format('Y-m-d'),
            'tanggal_lahir_formatted' => $this->tanggal_lahir?->format('d F Y'),
            'jenis_kelamin' => $this->jenis_kelamin,
            'jenis_kelamin_label' => $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            'alamat' => $this->alamat,
            'no_hp' => $this->no_hp,
            'email' => $this->email,
            'foto' => $this->foto,
            'foto_url' => $this->foto_url,

            // Kepegawaian
            'status_kepegawaian' => $this->status_kepegawaian instanceof StatusKepegawaian
                ? $this->status_kepegawaian->value
                : $this->status_kepegawaian,
            'status_kepegawaian_label' => $this->status_kepegawaian instanceof StatusKepegawaian
                ? $this->status_kepegawaian->label()
                : StatusKepegawaian::tryFrom($this->status_kepegawaian)?->label(),
            'status_kepegawaian_badge' => $this->status_kepegawaian instanceof StatusKepegawaian
                ? $this->status_kepegawaian->badgeColor()
                : StatusKepegawaian::tryFrom($this->status_kepegawaian)?->badgeColor(),
            'tanggal_mulai_kerja' => $this->tanggal_mulai_kerja?->format('Y-m-d'),
            'tanggal_mulai_kerja_formatted' => $this->tanggal_mulai_kerja?->format('d F Y'),
            'tanggal_berakhir_kontrak' => $this->tanggal_berakhir_kontrak?->format('Y-m-d'),
            'tanggal_berakhir_kontrak_formatted' => $this->tanggal_berakhir_kontrak?->format('d F Y'),
            'masa_kerja' => $this->getMasaKerja(),

            // Kualifikasi
            'kualifikasi_pendidikan' => $this->kualifikasi_pendidikan,

            // Status
            'is_active' => $this->is_active,
            'is_active_label' => $this->is_active ? 'Aktif' : 'Nonaktif',

            // Relationships
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'username' => $this->user->username,
                'status' => $this->user->status,
            ]),
            'subjects' => $this->whenLoaded('subjects', fn () => $this->subjects->map(fn ($subject) => [
                'id' => $subject->id,
                'kode_mapel' => $subject->kode_mapel,
                'nama_mapel' => $subject->nama_mapel,
                'is_primary' => $subject->pivot->is_primary ?? false,
            ])),
            'primary_subject_names' => $this->primary_subject_names,

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
