<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [1,'IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE.','197411281999032003','Pembina Utama Ahli Muda (IV/c)','Inspektur Daerah','S2 Perencanaan Kota dan Daerah, S2 Urban Managemen and Development','P','Inspektur'],
            [2,'HERY KRISTIONO, S.S.T.P., M.Kom.','198104081999121001','Pembina Tk. I (IV/b)','Sekretaris','S2 Komputer','L','Sekretariat'],
            [3,'SRI SURYANDARI, S.Sos.','196908271996032005','Pembina Tk. I (IV/b)','Irban I','S1 Administrasi Negara','P','Irban I'],
            [4,'SAIFUL HIDAYAT, S.S.T.P.','197911081998101001','Pembina Tk. I (IV/b)','Irban II','D-IV Pemerintahan','L','Irban II'],
            [5,'MUNADI, A.P.','197507011995011001','Pembina Tk. I (IV/b)','Irban III','D-IV Pemerintahan','L','Irban III'],
            [6,'IMAM TEGUH SUSATYO, S.E.','197011251995031005','Pembina Tk. I (IV/b)','Irban IV','S1 Ekonomi','L','Irban IV'],
            [7,'IFVO FERRYATAMA, S.S.T.P., M.Si. CFrA.','198102092000031001','Pembina Tk. I (IV/b)','IRBANSUS','S2 Pemerintahan','L','Irban Sus'],
            [8,'DIN AMALIYA DIAN FITRIYATI, S.E., M.M.','198304102010012026','Penata Tk.I (III/d)','Kasubbag. Administrasi dan Umum','S2 Manajemen','P','Sekretariat'],
            [9,'RIA SULISTYANTI, S.E., M.M.','198502012010012032','Pembina (IV/a)','Kasubbag. Perencanaan & Keuangan','S2 Manajemen','P','Sekretariat'],
            [10,'DWI HARJANTI, S.Psi.','197601112005012011','Penata Tk.I (III/d)','Kasubbag. Evaluasi dan Pelaporan','S1 Ilmu Psikologi','P','Sekretariat'],
            [11,'SLAMET WIDJAJA, S.H., M.H.','197009201998031009','Pembina Utama Ahli Muda (IV/c)','Auditor Ahli Madya','S2 Hukum','L','Irban Sus'],
            [12,'ARIFIN, S.H., S.I.P.','196710281993031007','Pembina Utama Ahli Muda (IV/c)','Auditor Ahli Madya','S1 Ilmu Hukum dan S1 Pemerintahan','L','Irban III'],
            [13,'KURNIA YAHYA, S.T.','197703232003121006','Pembina Tk. I (IV/b)','Auditor Ahli Madya','S1 Pengairan','L','Irban II'],
            [14,'YUSUF ARINTO, S.T.','197408192006041006','Pembina Tk. I (IV/b)','Auditor Ahli Madya','S1 Teknik Sipil','L','Irban Sus'],
            [15,'EMET TRINI DDU, S.E., S.I.P., M.Si.','197210231997032004','Pembina Tk. I (IV/b)','P2UPD Ahli Madya','S2 Manajeme Science','P','Irban III'],
            [16,'PARIJAN, S.E., M.M.','196803141991031014','Pembina (IV/a)','P2UPD Ahli Madya','S2 Manajemen','L','Irban I'],
            [17,'HERMIN DWI ASTUTI, S.E.','197401312006042012','Pembina (IV/a)','P2UPD Ahli Madya','S1 Manajemen Keuangan','P','Irban II'],
            [18,'HERMAWAN ARIANTO, S.Sos.','197003231997031005','Pembina (IV/a)','P2UPD Ahli Madya','S1 Pemerintahan','L','Irban IV'],
            [19,'AGUNG TRI PRASTIYO, S.E.','198208212005011006','Penata Tk.I (III/d)','Auditor Ahli Muda','S1 Ekonomi Akuntansi','L','Irban I'],
            [20,'MARIA MEGAHWATI OCI MEREBEAN, S.E., AKt.','198310062010012019','Penata (III/c)','Auditor Ahli Muda','S1 Ekonomi Akuntansi','P','Irban Sus'],
            [21,'ENDAH JULIASTUTI, S.E.','198007062010012011','Penata (III/c)','Auditor Ahli Muda','S1 Ekonomi Akuntansi','P','Irban II'],
            [22,'VIVI HERYANTI, S. Si.','198203282010012026','Penata (III/c)','Auditor Ahli Muda','S1 Kimia','P','Irban IV'],
            [23,'ELLIDA NURIYA PUTRI, S.H., M.Kn.','199506052018032001','Penata Ahli Muda Tk. I (III/b)','Auditor Ahli Pertama','S2 Ilmu Hukum','P','Irban Sus'],
            [24,'UMMU NAJWA, S.E.','198608232019022002','Penata Ahli Muda Tk. I (III/b)','Auditor Ahli Pertama','S1 Akuntansi','P','Irban Sus'],
            [25,'KHOIRUL ANWAR, S.Ak.','199601312019021002','Penata Ahli Muda Tk. I (III/b)','Auditor Ahli Pertama','S1 Akuntansi','L','Irban II'],
            [26,'RANI KRISTIANI, S.E.','198410252019022003','Penata Ahli Muda Tk. I (III/b)','Auditor Ahli Pertama','S1 Akuntansi','P','Irban II'],
            [27,'SITI AMINAH, S.E.','199207142019022002','Penata Ahli Muda Tk. I (III/b)','Auditor Ahli Pertama','S1 Akuntansi','P','Irban I'],
            [28,'NEILY RIF\'AH ANNAJAH, S.E.','198704272010012018','Penata Tk.I (III/d)','Auditor Ahli Pertama','S1 Ekonomi Akuntansi','P','Irban II'],
            [29,'ARIF RAHMAN SUTANTO, S.H.','199409172020121007','Penata Ahli Muda (III/a)','Auditor Ahli Pertama','S1 Ilmu Hukum','L','Irban II'],
            [30,'AJIE ISNANTO SAPUTRA, S.T.','198809292022031003','Penata Ahli Muda (III/a)','Auditor Ahli Pertama','S1 Teknik Sipil','L','Irban III'],
            [31,'TAUFIQUR RACHMAN, S.E.','199403262022031016','Penata Ahli Muda (III/a)','Auditor Ahli Pertama','S1 Ekonomi Akuntansi','L','Irban I'],
            [32,'NOFIANA DIAN KUSUMAWARDANI, S.H.','198711242022032006','Penata Ahli Muda (III/a)','Auditor Ahli Pertama','S1 Ilmu Hukum','P','Irban IV'],
            [33,'MUHAMMAD NUR FIKRI, S.T.','199510142022031012','Penata Ahli Muda (III/a)','Auditor Ahli Pertama','S1 Teknik Sipil','L','Irban III'],
            [34,'ANGGITA HENING PERTIWI, S.Ak.','199702172022032018','Penata Ahli Muda (III/a)','Auditor Ahli Pertama','S1 Akuntansi','P','Irban III'],
            [35,'AGUSTIANTO NURCAHYO, S.H.','199508212022031008','Penata Ahli Muda (III/a)','Auditor Ahli Pertama','S1 Ilmu Hukum','L','Irban IV'],
            [36,'PUNGKY RIONALDY, S.E.','198812152022031002','Penata Ahli Muda (III/a)','Auditor Ahli Pertama','S1 Akuntansi','L','Irban III'],
            [37,'JATI MANU KARTAMIHARJA, S.E.','199008292022031006','Penata Ahli Muda (III/a)','Auditor Ahli Pertama','S1 Akuntansi','L','Irban IV'],
            [38,'WAHYU BUDI CAHYANI, S.Ak.','199606222022032021','Penata Ahli Muda (III/a)','Auditor Ahli Pertama','S1 Akuntansi','P','Irban IV'],
            [39,'IKA PERTIWI, S.H.','198609082022032002','Penata Ahli Muda (III/a)','Auditor Ahli Pertama','S1 Ilmu Hukum','P','Irban I'],
            [40,'YENI WULANDARI, S.E.','199006082022032007','Penata Ahli Muda (III/a)','Auditor Ahli Pertama','S1 Akuntansi','P','Irban I'],
            [41,'BAGUS ARYA PRADANA, S.E.','199106122022031011','Penata Ahli Muda (III/a)','Auditor Ahli Pertama','S1 Akuntansi','L','Irban II'],
            [42,'REZKA FADILA, S.T.','199809032025051001','Penata Ahli Muda (III/a)','Auditor Ahli Pertama/CPNS','S1 Teknik Sipil','L','Irban II'],
            [43,'ADITYA ILHAM PRASTIKA, S.T.','199904132025051002','Penata Ahli Muda (III/a)','Auditor Ahli Pertama/CPNS','S1 Teknik Sipil','L','Irban Sus'],
            [44,'ELBY AINUNHABIB, S.T.','199806152025051001','Penata Ahli Muda (III/a)','Auditor Ahli Pertama/CPNS','S1 Teknik Sipil','L','Irban III'],
            [45,'DEWI ZAHRO WAHYUNINGTYAS, S.T.','200105242025052004','Penata Ahli Muda (III/a)','Auditor Ahli Pertama/CPNS','S1 Teknik Sipil','P','Irban I'],
            [46,'KARINA IZZA AZ ZAHROH, S.T.','200105042025052004','Penata Ahli Muda (III/a)','Auditor Ahli Pertama/CPNS','S1 Teknik Sipil','P','Irban IV'],
            [47,'ADETIYA SEKAR MAHARANI, S.Sos.','199804202022032028','Penata Ahli Muda (III/a)','P2UPD Ahli Pertama','S1 Ilmu Hubungan Luar Negeri','P','Irban IV'],
            [48,'DIANITA RAHMA NUGRAHENI, S.H.','199711042022032018','Penata Ahli Muda (III/a)','P2UPD Ahli Pertama','S1 Hukum','P','Irban III'],
            [49,'SIHONO, S.I.P.','199607032022031007','Penata Ahli Muda (III/a)','P2UPD Ahli Pertama','S1 Ilmu Pemerintahan','L','Irban I'],
            [50,'PRASETYO SITOWIN, S.I.P.','199712242022031006','Penata Ahli Muda (III/a)','P2UPD Ahli Pertama','S1 Ilmu Pemerintahan','L','Irban IV'],
            [51,'WAHYU ANJAR, S.Tr.I.P.','200007272024092001','Penata Ahli Muda (III/a)','P2UPD Ahli Pertama','D-IV Studi Kependudukan dan Pencatatan Sipil','P','Irban I'],
            [52,'VITA AYU HENDRYASARI, S.Tr.I.P.','200110092023082001','Penata Ahli Muda (III/a)','P2UPD Ahli Pertama','D-IV Administrasi Pemerintahan Daerah','P','Irban Sus'],
            [53,'NURUL HANA\' SALSABIL, S.Ak.','200204212025052001','Penata Ahli Muda (III/a)','P2UPD Ahli Pertama/CPNS','S1 Akuntansi','P','Irban II'],
            [54,'UUT INDRIANI, S.E.','199907222025052003','Penata Ahli Muda (III/a)','P2UPD Ahli Pertama/CPNS','S1 Ekonomi Pembangunan','P','Irban IV'],
            [55,'BAGUS DWI ARYA FEBRIYANTO, S.H.','200202012025051002','Penata Ahli Muda (III/a)','P2UPD Ahli Pertama/CPNS','S1 Ilmu Hukum','L','Irban Sus'],
            [56,'BUNGA NADIA TARUNI, S.E.','200207282025052004','Penata Ahli Muda (III/a)','P2UPD Ahli Pertama/CPNS','S1 Ekonomi Pembangunan','P','Irban I'],
            [57,'WAHYU WIDIARTANTI, S.Ak.','197910102009032005','Penata (III/c)','Auditor Ahli Pertama','S1 Akuntansi','P','Irban II'],
            [58,'DIAH MAS\'UDAH, S.Kom.','199206272023212054','PPPK (Kelas Jabatan IX)','Pranata Komputer Ahli Pertama','S1 Ilmu Komputer','P','Sekretariat'],
            [59,'ISMU ADHIM, S.Kom.','198609302024211004','PPPK (Kelas Jabatan IX)','Pranata Komputer Ahli Pertama','S1 Ilmu Komputer','L','Sekretariat'],
            [60,'MOH. MAULANA FIRDAUS, S.T.','199511262025211023','PPPK (Kelas Jabatan IX)','Arsiparis Ahli Pertama','S1 Teknik Industri','L','Sekretariat'],
            [61,'CHODRYNA LATIFUN NISA, S.Pd.','199506262025212056','PPPK (Kelas Jabatan IX)','Arsiparis Ahli Pertama','S1 Pendidikan Ekonomi Akuntansi','P','Sekretariat'],
            [62,'RILLIA LAZUARDIANA, S.E.','199404182025212049','PPPK (Kelas Jabatan IX)','Penata Layanan Operasional','S1 Agribisnis','P','Sekretariat'],
            [63,'DIMAS FERRY PRASETYO, S.M.','199605132025211020','PPPK (Kelas Jabatan IX)','Penata Layanan Operasional','S1 Manajemen','L','Sekretariat'],
            [64,'HARY CANDRA SAPUTRA, S.H.','199203072025211037','PPPK (Kelas Jabatan IX)','Penata Layanan Operasional','S1 Ilmu Hukum','L','Sekretariat'],
            [65,'TEDDY SEPTIANTO, S.M.','199209072025211035','PPPK (Kelas Jabatan IX)','Penata Layanan Operasional','S1 Manajemen','L','Sekretariat'],
            [66,'FITRA ATMAJAYA FEBRI MAULANA, S.T.','199602252025211025','PPPK (Kelas Jabatan IX)','Penata Layanan Operasional','S1 Teknik Sipil','L','Sekretariat'],
            [67,'NANDA TRIAS RAMADHANI, S.M.','199701202025211026','PPPK (Kelas Jabatan IX)','Penata Layanan Operasional','S1 Manajemen','L','Sekretariat'],
            [68,'ALDA SABILATUL KHOIROT ASYAFIK, A.Md.M.','199901202025212009','PPPK (Kelas Jabatan VII)','Pengelola Layanan Operasional','D3 Administrasi Pajak','P','Sekretariat'],
            [69,'ACHID SINIANTO','199103032025211048','PPPK (Kelas Jabatan V)','Pengadministrasi Perkantoran','SMA Sederajat','L','Sekretariat'],
            [70,'MUHAMMAD KHORIB','199609172025211031','PPPK (Kelas Jabatan V)','Operator Layanan Operasional','SMA Sederajat','L','Sekretariat'],
            [71,'CITRA SUGIANTO','198205062025211050','PPPK (Kelas Jabatan V)','Operator Layanan Operasional','SMA Sederajat','L','Sekretariat'],
            [72,'EKO BAMBANG PURNOMO','197105282025211008','PPPK (Kelas Jabatan V)','Operator Layanan Operasional','SMA Sederajat','L','Sekretariat'],
            [73,'ANDRI KURNIAWAN','199204012025211047','PPPK (Kelas Jabatan I)','Pengelola Umum Operasional','SMP Sederajat','L','Sekretariat'],
        ];

        foreach ($data as $row) {
            User::updateOrCreate(
                ['nip' => $row[2]],
                [
                    'name' => $row[1],
                    'email' => Str::slug($row[1]) . '@inspektorat.go.id',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password123'),
                    'nip' => $row[2],
                    'pangkat_gol' => $row[3],
                    'jabatan' => $row[4],
                    'pendidikan_terakhir' => $row[5],
                    'jenis_kelamin' => $row[6],
                    'unit_kerja' => $row[7],
                    'phone' => null,
                    'avatar' => null,
                    'is_active' => true,
                    'remember_token' => Str::random(10),
                ]
            );
        }
    }
}