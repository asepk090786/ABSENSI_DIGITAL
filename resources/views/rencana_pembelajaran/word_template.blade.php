<div>
    <p style="text-align: center; font-size: 8pt; color: #526579;">MODUL AJAR</p>
    <h1 style="text-align: center; font-size: 20pt; color: #16283c;">MODUL AJAR</h1>

    <h2 style="font-size: 13pt; color: #16283c;">Informasi Umum</h2>
    <table style="width: 100%; border-collapse: collapse;">
        <tbody>
            <tr>
                <td style="width: 30%; border: 1px solid #afbdce; background-color: #eef5fc; padding: 6px;"><strong>Judul Modul Ajar</strong></td>
                <td style="border: 1px solid #afbdce; padding: 6px;">{{ $data['title'] }}</td>
            </tr>
            <tr>
                <td style="width: 30%; border: 1px solid #afbdce; background-color: #eef5fc; padding: 6px;"><strong>Mata Pelajaran</strong></td>
                <td style="border: 1px solid #afbdce; padding: 6px;">{{ $data['subject'] }}</td>
            </tr>
            <tr>
                <td style="width: 30%; border: 1px solid #afbdce; background-color: #eef5fc; padding: 6px;"><strong>Kelas / Fase</strong></td>
                <td style="border: 1px solid #afbdce; padding: 6px;">{{ $data['class'] }}</td>
            </tr>
            <tr>
                <td style="width: 30%; border: 1px solid #afbdce; background-color: #eef5fc; padding: 6px;"><strong>Status</strong></td>
                <td style="border: 1px solid #afbdce; padding: 6px;">{{ $data['status'] === 'published' ? 'Publish (Digunakan untuk KBM)' : 'Draft (Belum digunakan untuk KBM)' }}</td>
            </tr>
            <tr>
                <td style="width: 30%; border: 1px solid #afbdce; background-color: #eef5fc; padding: 6px;"><strong>Alokasi Waktu</strong></td>
                <td style="border: 1px solid #afbdce; padding: 6px;">{{ $data['duration'] }}</td>
            </tr>
            <tr>
                <td style="width: 30%; border: 1px solid #afbdce; background-color: #eef5fc; padding: 6px;"><strong>Dimensi Lulusan</strong></td>
                <td style="border: 1px solid #afbdce; padding: 6px;">{{ $data['dimensi_lulusan'] }}</td>
            </tr>
        </tbody>
    </table>

    <h2 style="font-size: 14pt; color: #16283c;">Capaian Pembelajaran</h2>
    <p>{{ $data['achievement'] }}</p>
    <h2 style="font-size: 14pt; color: #16283c;">Tujuan Pembelajaran</h2>
    <p>{{ $data['objectives'] }}</p>
    <h2 style="font-size: 14pt; color: #16283c;">Metode Pembelajaran</h2>
    <p>{{ $data['methods'] }}</p>
    <h2 style="font-size: 14pt; color: #16283c;">Praktik Pedagogis</h2>
    <p>{{ $data['practice'] }}</p>
    <h2 style="font-size: 14pt; color: #16283c;">Lingkungan Pembelajaran</h2>
    <p>{{ $data['environment'] }}</p>
    <h2 style="font-size: 14pt; color: #16283c;">Pemanfaatan Digital</h2>
    <p>{{ $data['digital'] }}</p>
    <h2 style="font-size: 14pt; color: #16283c;">Pengalaman Pembelajaran</h2>
    <p>{{ $data['experience'] }}</p>
    <h2 style="font-size: 14pt; color: #16283c;">Refleksi Pembelajaran</h2>
    <p>{{ $data['reflection'] }}</p>
    <h2 style="font-size: 14pt; color: #16283c;">Asesmen</h2>
    <p>{{ $data['assessment'] }}</p>
</div>
