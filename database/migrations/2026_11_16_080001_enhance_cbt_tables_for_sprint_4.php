<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expand exams table
        Schema::table('exams', function (Blueprint $table) {
            if (! Schema::hasColumn('exams', 'scoring_rule')) {
                $table->enum('scoring_rule', ['standard', 'partial'])->default('standard')->after('max_violations');
            }
            if (! Schema::hasColumn('exams', 'randomize_questions')) {
                $table->boolean('randomize_questions')->default(true)->after('scoring_rule');
            }
            if (! Schema::hasColumn('exams', 'randomize_options')) {
                $table->boolean('randomize_options')->default(true)->after('randomize_questions');
            }
            if (! Schema::hasColumn('exams', 'is_remedial')) {
                $table->boolean('is_remedial')->default(false)->after('randomize_options');
            }
        });

        // 2. Remedial student targets table
        if (! Schema::hasTable('exam_student_targets')) {
            Schema::create('exam_student_targets', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('exam_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['exam_id', 'student_id']);
            });
        }

        // 3. Expand questions table
        Schema::table('questions', function (Blueprint $table) {
            if (! Schema::hasColumn('questions', 'image_path')) {
                $table->string('image_path')->nullable()->after('content');
            }
            if (! Schema::hasColumn('questions', 'metadata')) {
                $table->json('metadata')->nullable()->after('image_path');
            }
        });

        // 4. Expand options table
        Schema::table('options', function (Blueprint $table) {
            if (! Schema::hasColumn('options', 'image_path')) {
                $table->string('image_path')->nullable()->after('content');
            }
            if (! Schema::hasColumn('options', 'pair_match')) {
                $table->text('pair_match')->nullable()->after('image_path');
            }
        });

        // 5. Expand exam_attempts table
        Schema::table('exam_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('exam_attempts', 'question_order')) {
                $table->json('question_order')->nullable()->after('answers');
            }
            if (! Schema::hasColumn('exam_attempts', 'seed')) {
                $table->unsignedBigInteger('seed')->nullable()->after('question_order');
            }
            if (! Schema::hasColumn('exam_attempts', 'extra_time_minutes')) {
                $table->integer('extra_time_minutes')->default(0)->after('seed');
            }
            if (! Schema::hasColumn('exam_attempts', 'is_unlocked')) {
                $table->boolean('is_unlocked')->default(false)->after('extra_time_minutes');
            }
            if (! Schema::hasColumn('exam_attempts', 'manual_essay_scores')) {
                $table->json('manual_essay_scores')->nullable()->after('is_unlocked');
            }
            if (! Schema::hasColumn('exam_attempts', 'needs_manual_grading')) {
                $table->boolean('needs_manual_grading')->default(false)->after('manual_essay_scores');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_student_targets');

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['scoring_rule', 'randomize_questions', 'randomize_options', 'is_remedial']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'metadata']);
        });

        Schema::table('options', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'pair_match']);
        });

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn(['question_order', 'seed', 'extra_time_minutes', 'is_unlocked', 'manual_essay_scores', 'needs_manual_grading']);
        });
    }
};
