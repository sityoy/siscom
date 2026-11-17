<?php

namespace App\Support;

final class JobTitleTranslator
{
    /** @var array<string, string> */
    private const INDONESIAN_TO_ENGLISH = [
        // Sekolah dan yayasan
        'kepala sekolah' => 'Principal',
        'kepala smk' => 'Principal',
        'kepala sma' => 'Principal',
        'kepala smp' => 'Principal',
        'kepala sd' => 'Principal',
        'kepala madrasah' => 'Principal',
        'kepala mi' => 'Principal',
        'kepala mts' => 'Principal',
        'kepala ma' => 'Principal',
        'wakil kepala sekolah' => 'Vice Principal',
        'wakasek' => 'Vice Principal',
        'wakasek kurikulum' => 'Vice Principal for Curriculum',
        'wakil kepala sekolah bidang kurikulum' => 'Vice Principal for Curriculum',
        'wakasek kesiswaan' => 'Vice Principal for Student Affairs',
        'wakil kepala sekolah bidang kesiswaan' => 'Vice Principal for Student Affairs',
        'wakasek sarpras' => 'Vice Principal for Facilities and Infrastructure',
        'wakasek sarana prasarana' => 'Vice Principal for Facilities and Infrastructure',
        'wakil kepala sekolah bidang sarana prasarana' => 'Vice Principal for Facilities and Infrastructure',
        'wakasek humas' => 'Vice Principal for Public Relations',
        'wakil kepala sekolah bidang humas' => 'Vice Principal for Public Relations',
        'wakasek hubin' => 'Vice Principal for Industry Relations',
        'wakil kepala sekolah bidang hubungan industri' => 'Vice Principal for Industry Relations',
        'ketua yayasan' => 'Foundation Chairperson',
        'pembina yayasan' => 'Foundation Advisor',
        'pengawas yayasan' => 'Foundation Supervisor',
        'kepala tata usaha' => 'Head of Administration',
        'kepala tu' => 'Head of Administration',
        'bendahara sekolah' => 'School Treasurer',
        'operator sekolah' => 'School Operator',
        'guru' => 'Teacher',
        'wali kelas' => 'Homeroom Teacher',
        'ketua panitia' => 'Committee Chairperson',
        'sekretaris panitia' => 'Committee Secretary',
        'pelanggan' => 'Customer',
        'klien' => 'Client',
        'client' => 'Client',
        'perwakilan pelanggan' => 'Customer Representative',
        'perwakilan klien' => 'Client Representative',
        'perwakilan client' => 'Client Representative',

        // Perusahaan dan organisasi
        'komisaris utama' => 'President Commissioner',
        'komisaris' => 'Commissioner',
        'direktur utama' => 'President Director',
        'direktur' => 'Director',
        'direktur/pimpinan' => 'Director',
        'direktur pelaksana' => 'Managing Director',
        'direktur operasional' => 'Operations Director',
        'direktur keuangan' => 'Finance Director',
        'direktur pemasaran' => 'Marketing Director',
        'direktur teknologi' => 'Technology Director',
        'pimpinan' => 'Director',
        'manajer umum' => 'General Manager',
        'manajer' => 'Manager',
        'manajer operasional' => 'Operations Manager',
        'manajer keuangan' => 'Finance Manager',
        'manajer pemasaran' => 'Marketing Manager',
        'manajer sdm' => 'Human Resources Manager',
        'manajer sumber daya manusia' => 'Human Resources Manager',
        'manajer teknologi informasi' => 'Information Technology Manager',
        'manajer ti' => 'Information Technology Manager',
        'manajer it' => 'Information Technology Manager',
        'manajer proyek' => 'Project Manager',
        'manajer layanan' => 'Service Manager',
        'kepala divisi' => 'Division Head',
        'kepala bagian' => 'Department Head',
        'kepala cabang' => 'Branch Manager',
        'supervisor' => 'Supervisor',
        'koordinator' => 'Coordinator',
        'sekretaris' => 'Secretary',
        'bendahara' => 'Treasurer',
        'administrator' => 'Administrator',
        'staf' => 'Staff',
        'pemilik' => 'Owner',
        'pemilik usaha' => 'Owner',
        'pendiri' => 'Founder',
        'konsultan' => 'Consultant',
        'penanggung jawab' => 'Person in Charge',
        'ceo' => 'Chief Executive Officer',
        'coo' => 'Chief Operating Officer',
        'cfo' => 'Chief Financial Officer',
        'cto' => 'Chief Technology Officer',

        // Pengadaan dan keuangan pemerintah/sekolah
        'pejabat pembuat komitmen' => 'Commitment-Making Officer',
        'ppk' => 'Commitment-Making Officer',
        'pejabat pengadaan' => 'Procurement Officer',
        'pengguna anggaran' => 'Budget User',
        'pa' => 'Budget User',
        'kuasa pengguna anggaran' => 'Budget User Authority',
        'kpa' => 'Budget User Authority',
    ];

    /** @var array<string, string> */
    private const ENGLISH_TO_INDONESIAN = [
        'principal' => 'Kepala Sekolah',
        'vice principal' => 'Wakil Kepala Sekolah',
        'vice principal for curriculum' => 'Wakasek Kurikulum',
        'vice principal for student affairs' => 'Wakasek Kesiswaan',
        'vice principal for facilities and infrastructure' => 'Wakasek Sarana Prasarana',
        'vice principal for public relations' => 'Wakasek Humas',
        'vice principal for industry relations' => 'Wakasek Hubin',
        'foundation chairperson' => 'Ketua Yayasan',
        'foundation advisor' => 'Pembina Yayasan',
        'foundation supervisor' => 'Pengawas Yayasan',
        'head of administration' => 'Kepala Tata Usaha',
        'school treasurer' => 'Bendahara Sekolah',
        'school operator' => 'Operator Sekolah',
        'teacher' => 'Guru',
        'homeroom teacher' => 'Wali Kelas',
        'committee chairperson' => 'Ketua Panitia',
        'committee secretary' => 'Sekretaris Panitia',
        'customer' => 'Pelanggan',
        'client' => 'Klien',
        'customer representative' => 'Perwakilan Pelanggan',
        'client representative' => 'Perwakilan Klien',
        'president commissioner' => 'Komisaris Utama',
        'commissioner' => 'Komisaris',
        'president director' => 'Direktur Utama',
        'director' => 'Direktur',
        'managing director' => 'Direktur Pelaksana',
        'operations director' => 'Direktur Operasional',
        'finance director' => 'Direktur Keuangan',
        'marketing director' => 'Direktur Pemasaran',
        'technology director' => 'Direktur Teknologi',
        'general manager' => 'Manajer Umum',
        'manager' => 'Manajer',
        'operations manager' => 'Manajer Operasional',
        'finance manager' => 'Manajer Keuangan',
        'marketing manager' => 'Manajer Pemasaran',
        'human resources manager' => 'Manajer Sumber Daya Manusia',
        'information technology manager' => 'Manajer Teknologi Informasi',
        'project manager' => 'Manajer Proyek',
        'service manager' => 'Manajer Layanan',
        'division head' => 'Kepala Divisi',
        'department head' => 'Kepala Bagian',
        'branch manager' => 'Kepala Cabang',
        'supervisor' => 'Supervisor',
        'coordinator' => 'Koordinator',
        'secretary' => 'Sekretaris',
        'treasurer' => 'Bendahara',
        'administrator' => 'Administrator',
        'staff' => 'Staf',
        'owner' => 'Pemilik',
        'founder' => 'Pendiri',
        'consultant' => 'Konsultan',
        'person in charge' => 'Penanggung Jawab',
        'chief executive officer' => 'Direktur Utama',
        'chief operating officer' => 'Direktur Operasional',
        'chief financial officer' => 'Direktur Keuangan',
        'chief technology officer' => 'Direktur Teknologi',
        'commitment-making officer' => 'Pejabat Pembuat Komitmen',
        'procurement officer' => 'Pejabat Pengadaan',
        'budget user' => 'Pengguna Anggaran',
        'budget user authority' => 'Kuasa Pengguna Anggaran',
    ];

    public static function translate(?string $title, string $language): string
    {
        $title = trim((string) $title);

        if ($title === '') {
            return '';
        }

        $normalizedTitle = self::normalize($title);
        $translations = $language === 'en'
            ? self::INDONESIAN_TO_ENGLISH
            : self::ENGLISH_TO_INDONESIAN;

        return $translations[$normalizedTitle] ?? $title;
    }

    private static function normalize(string $title): string
    {
        return mb_strtolower(
            preg_replace('/\s+/u', ' ', trim($title)) ?? trim($title),
        );
    }
}
