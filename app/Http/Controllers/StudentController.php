<?php

namespace App\Http\Controllers;

use App\Imports\StudentImport;
use App\Models\Student;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Classes;
use App\Models\Collection;
use Illuminate\Http\Request;
use App\Models\ClassApproval;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function User(Request $request)
    {
        $search = $request->input('search_student');

        $students = User::join('students', 'users.id', '=', 'students.user_id')
            ->role('Murid')
            ->whereIn('students.status', ['siswa', 'lulus'])
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', '!=', 'Murid');
            })
            ->where(function ($query) use ($search) {
                $query->whereHas('classes', function ($q) use ($search) {
                    $q->where('name_class', 'LIKE', '%' . $search . '%');
                })
                    // ->orWhereHas('subject', function ($q) use ($search) {
                    //     $q->where('name_subject', 'LIKE', '%' . $search . '%');
                    // })
                    ->orWhere('users.name', 'LIKE', '%' . $search . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $search . '%');
            })
            ->leftJoin('class_approvals', 'users.id', '=', 'class_approvals.user_id')
            ->select('users.*', 'students.status as student_status', 'class_approvals.status as approval_status')
            ->orderByRaw("class_approvals.user_id IS NULL ASC")
            ->orderByRaw("FIELD(class_approvals.status, 'pending', 'approved', 'rejected') ASC")
            ->orderBy('users.created_at', 'desc')
            ->paginate(5);

        // Update otomatis siswa jadi 'lulus'
        foreach ($students as $student) {
            $createdAt = Carbon::parse($student->created_at);
            $graduationDate = $createdAt->addYears(3);

            if (Carbon::now()->greaterThanOrEqualTo($graduationDate)) {
                DB::table('students')
                    ->where('teacher_id', $student->id)
                    ->update([
                        'status' => 'lulus',
                        'graduation_date' => $graduationDate,
                        'updated_at' => now()
                    ]);
            }
        }

        $approvals = ClassApproval::all();
        $classes = Classes::all();

        return view('Admins.Students.index', compact('students', 'classes' ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nis' => 'required|string',
            'class_id' => 'required|exists:classes,id',
        ], [
            'class_id.required' => 'Kelas wajib dipilih.',
        ]);

        // 1. Buat user murid
        $student = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'plain_password' => $request->password,
            'nis' => $request->nis,
            'status' => 'siswa',
        ]);

        // 2. Role Murid
        $student->assignRole('Murid');

        // 3. Masukkan ke tabel students
        \DB::table('students')->insert([
            'user_id' => $student->id,
            'nis' => $request->nis,
            'class_id' => is_array($request->class_id) ? $request->class_id[0] : $request->class_id,
            'status' => 'siswa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Assign kelas di teacher_classes jika dibutuhkan
        DB::table('teacher_classes')->insert([
            'teacher_id' => $student->id,
            'classes_id' => is_array($request->class_id) ? $request->class_id[0] : $request->class_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Murid berhasil ditambahkan ke kelas.');
    }

    public function import(Request $request)
    {
        Excel::import(new StudentImport(), $request->file('file'));

        return back()->with('success', 'Import murid berhasil!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'class_id' => 'required|exists:classes,id',
        ]);

        $student = User::findOrFail($id);

        // Update data user
        $student->name = $request->name;
        $student->email = $request->email;

        if ($request->password) {
            $student->password = bcrypt($request->password);
        }

        $student->save();

        // Update kelas
        DB::table('teacher_classes')
            ->where('teacher_id', $id)
            ->update([
                'classes_id' => $request->class_id,
                'updated_at' => now(),
            ]);

        return redirect()->route('Students')->with('success', 'Data murid berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $student = User::findOrFail($id);

        // Hapus relasi kelas
        DB::table('teacher_classes')->where('teacher_id', $id)->delete();

        // Hapus user
        $student->delete();

        return redirect()->back()->with('success', 'Murid berhasil dihapus.');
    }

}
