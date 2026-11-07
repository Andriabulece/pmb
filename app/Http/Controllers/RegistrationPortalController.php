<?php

namespace App\Http\Controllers;

use App\Models\PaymentConfirmation;
use App\Models\Registration;
use App\Models\StudyProgram;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationPortalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $registration = Registration::with(['studyProgram', 'assessments', 'paymentConfirmation'])
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $studyPrograms = StudyProgram::orderBy('degree')->orderBy('name')->get();

        return view('pmb.portal', compact('registration', 'studyPrograms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'study_program_id' => 'required|exists:study_programs,id',
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:30',
            'gender' => 'required|string|in:L,P',
            'birth_place' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'address' => 'required|string|max:1000',
            'previous_school' => 'required|string|max:255',
        ]);

        $regNumber = 'PMB-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));

        $registration = Registration::create([
            'registration_number' => $regNumber,
            'user_id' => Auth::id(),
            'study_program_id' => $validated['study_program_id'],
            'full_name' => $validated['full_name'],
            'phone_number' => $validated['phone_number'],
            'gender' => $validated['gender'],
            'birth_place' => $validated['birth_place'],
            'birth_date' => $validated['birth_date'],
            'address' => $validated['address'],
            'previous_school' => $validated['previous_school'],
            'status' => 'draft',
        ]);

        return redirect()->route('pmb.portal')->with('success', 'Formulir pendaftaran berhasil dibuat! Silakan unggah berkas persyaratan.');
    }

    public function uploadDocuments(Request $request, Registration $registration)
    {
        if ($registration->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'ktp' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'transkrip_nilai' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'pas_foto' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('ktp')) {
            $registration->addMediaFromRequest('ktp')->toMediaCollection('ktp');
        }
        if ($request->hasFile('ijazah')) {
            $registration->addMediaFromRequest('ijazah')->toMediaCollection('ijazah');
        }
        if ($request->hasFile('transkrip_nilai')) {
            $registration->addMediaFromRequest('transkrip_nilai')->toMediaCollection('transkrip_nilai');
        }
        if ($request->hasFile('pas_foto')) {
            $registration->addMediaFromRequest('pas_foto')->toMediaCollection('pas_foto');
        }

        // Jika semua berkas minimal ada, ubah status jadi submitted
        if ($registration->hasMedia('ktp') && $registration->hasMedia('ijazah') && $registration->hasMedia('pas_foto')) {
            $registration->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);
        }

        return redirect()->route('pmb.portal')->with('success', 'Dokumen berhasil diunggah!');
    }

    public function uploadPayment(Request $request, Registration $registration)
    {
        if ($registration->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000',
            'payment_date' => 'required|date',
            'bank_name' => 'required|string|max:100',
            'account_holder' => 'required|string|max:100',
            'bukti_transfer' => 'required|file|mimes:jpg,jpeg,png,pdf|max:3072',
        ]);

        $payment = PaymentConfirmation::updateOrCreate(
            ['registration_id' => $registration->id],
            [
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'bank_name' => $validated['bank_name'],
                'account_holder' => $validated['account_holder'],
                'verification_status' => 'pending',
            ]
        );

        if ($request->hasFile('bukti_transfer')) {
            $payment->addMediaFromRequest('bukti_transfer')->toMediaCollection('bukti_transfer');
        }

        $registration->update(['status' => 'payment_submitted']);

        return redirect()->route('pmb.portal')->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi tim Keuangan & Akademik.');
    }

    public function printAcceptance(Registration $registration)
    {
        if ($registration->user_id !== Auth::id() && !Auth::user()->canAccessPanel(filament()->getDefaultPanel())) {
            abort(403);
        }

        if ($registration->status !== 'accepted') {
            abort(400, 'Pendaftaran belum diterima secara resmi.');
        }

        $pdf = Pdf::loadView('pmb.acceptance_letter', compact('registration'));
        return $pdf->stream('Surat_Penerimaan_' . $registration->registration_number . '.pdf');
    }
}
