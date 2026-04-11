<?php

namespace App\Http\Controllers;

use App\Models\KycRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KycController extends Controller
{
    public function index()
    {
        $kyc = Auth::user()->kycRequest;
        return view('user.kyc', ['kyc' => $kyc]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_front' => 'required|image|max:5120',
            'id_back' => 'required|image|max:5120',
            'selfie' => 'required|image|max:5120',
        ]);

        $user = Auth::user();
        
        $paths = [];
        $paths['id_front_path'] = $request->file('id_front')->store('kyc', 'public');
        $paths['id_back_path'] = $request->file('id_back')->store('kyc', 'public');
        $paths['selfie_path'] = $request->file('selfie')->store('kyc', 'public');

        $user->kycRequest()->updateOrCreate([], array_merge($paths, ['status' => 'pending']));
        $user->update(['status_kyc' => 'pending']);

        return redirect()->route('dashboard')->with('ok', 'Dokumen KYC telah diterima dan sedang disemak.');
    }
}
