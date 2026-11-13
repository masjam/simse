<?php

$files = glob('database/migrations/2026_09_30_045235_add_details_to_students_table.php');
if ($files) {
    file_put_contents($files[0], '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(\'students\', function (Blueprint $table) {
            $table->enum(\'gender\', [\'L\', \'P\'])->nullable();
            $table->string(\'kewarganegaraan\')->nullable()->default(\'ID\');
            $table->string(\'no_kk\')->nullable();
            $table->string(\'tempat_lahir\')->nullable();
            $table->date(\'tanggal_lahir\')->nullable();
            $table->string(\'no_registrasi_akta_lahir\')->nullable();
            $table->string(\'berkebutuhan_khusus\')->nullable();
            $table->string(\'agama\')->nullable();
            $table->string(\'alamat_jalan\')->nullable();
            $table->string(\'rt\', 10)->nullable();
            $table->string(\'rw\', 10)->nullable();
            $table->string(\'dusun\')->nullable();
            $table->string(\'desa_kelurahan\')->nullable();
            $table->string(\'kode_pos\', 10)->nullable();
            $table->string(\'lintang\')->nullable();
            $table->string(\'bujur\')->nullable();
            $table->string(\'tempat_tinggal\')->nullable();
            $table->string(\'moda_transportasi\')->nullable();
            $table->integer(\'anak_ke\')->nullable();
            
            // Data Ayah
            $table->string(\'nama_ayah\')->nullable();
            $table->string(\'nik_ayah\')->nullable();
            $table->string(\'tahun_lahir_ayah\', 4)->nullable();
            $table->string(\'pendidikan_ayah\')->nullable();
            $table->string(\'pekerjaan_ayah\')->nullable();
            $table->string(\'berkebutuhan_khusus_ayah\')->nullable();
            $table->string(\'penghasilan_ayah\')->nullable();
            
            // Data Ibu
            $table->enum(\'status_hidup_ibu\', [\'Hidup\', \'Wafat\'])->nullable();
            $table->string(\'nama_ibu\')->nullable();
            $table->string(\'nik_ibu\')->nullable();
            $table->string(\'tahun_lahir_ibu\', 4)->nullable();
            $table->string(\'pendidikan_ibu\')->nullable();
            $table->string(\'pekerjaan_ibu\')->nullable();
            $table->string(\'berkebutuhan_khusus_ibu\')->nullable();
            $table->string(\'penghasilan_ibu\')->nullable();
            
            // Data Wali
            $table->boolean(\'mempunyai_wali\')->default(false);
            $table->string(\'nama_wali\')->nullable();
            $table->string(\'nik_wali\')->nullable();
            $table->string(\'tahun_lahir_wali\', 4)->nullable();
            $table->string(\'pendidikan_wali\')->nullable();
            $table->string(\'pekerjaan_wali\')->nullable();
            $table->string(\'penghasilan_wali\')->nullable();
            
            // Kontak
            $table->string(\'no_telepon_rumah\')->nullable();
            $table->string(\'no_hp\')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(\'students\', function (Blueprint $table) {
            $table->dropColumn([
                \'gender\', \'kewarganegaraan\', \'no_kk\', \'tempat_lahir\', \'tanggal_lahir\',
                \'no_registrasi_akta_lahir\', \'berkebutuhan_khusus\', \'agama\', \'alamat_jalan\',
                \'rt\', \'rw\', \'dusun\', \'desa_kelurahan\', \'kode_pos\', \'lintang\', \'bujur\',
                \'tempat_tinggal\', \'moda_transportasi\', \'anak_ke\',
                \'nama_ayah\', \'nik_ayah\', \'tahun_lahir_ayah\', \'pendidikan_ayah\', \'pekerjaan_ayah\',
                \'berkebutuhan_khusus_ayah\', \'penghasilan_ayah\',
                \'status_hidup_ibu\', \'nama_ibu\', \'nik_ibu\', \'tahun_lahir_ibu\', \'pendidikan_ibu\',
                \'pekerjaan_ibu\', \'berkebutuhan_khusus_ibu\', \'penghasilan_ibu\',
                \'mempunyai_wali\', \'nama_wali\', \'nik_wali\', \'tahun_lahir_wali\', \'pendidikan_wali\',
                \'pekerjaan_wali\', \'penghasilan_wali\',
                \'no_telepon_rumah\', \'no_hp\'
            ]);
        });
    }
};');
}

$files = glob('database/migrations/2026_09_30_045244_create_student_achievements_table.php');
if ($files) {
    file_put_contents($files[0], '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(\'student_achievements\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'student_id\')->constrained()->cascadeOnDelete();
            $table->string(\'name\');
            $table->string(\'level\');
            $table->integer(\'year\');
            $table->text(\'description\')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(\'student_achievements\');
    }
};');
}

$files = glob('database/migrations/2026_09_30_045300_create_student_periodics_table.php');
if ($files) {
    file_put_contents($files[0], '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(\'student_periodics\', function (Blueprint $table) {
            $table->id();
            $table->foreignId(\'student_id\')->constrained()->cascadeOnDelete();
            $table->float(\'tinggi_badan\')->nullable();
            $table->float(\'berat_badan\')->nullable();
            $table->float(\'lingkar_kepala\')->nullable();
            $table->date(\'recorded_at\')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(\'student_periodics\');
    }
};');
}

echo "Done\n";
