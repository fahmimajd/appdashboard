<?php

namespace App\Http\Controllers;

use App\Models\VpnDesa;
use App\Models\WilayahDesa;
use Illuminate\Http\Request;

class VpnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $query = VpnDesa::with('desa.kecamatan');

        if ($request->has('search')) {
            $search = $request->search;
            $query->leftJoin('wilayah_desa', 'vpn_desa.kode_desa', '=', 'wilayah_desa.kode_desa')
                  ->where(function($q) use ($search) {
                      $q->where('vpn_desa.username', 'like', "%{$search}%")
                        ->orWhere('wilayah_desa.nama_desa', 'like', "%{$search}%");
                  })
                  ->select('vpn_desa.*'); // Ensure we get VPN models
        }

        if ($request->has('desa_id') && $request->desa_id != 'all') {
            $query->where('kode_desa', $request->desa_id);
        }
        
        $vpns = $query->paginate(15)->withQueryString();
        $desas = WilayahDesa::orderBy('nama_desa')->get();

        return view('vpn.index', compact('vpns', 'desas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        return view('vpn.create', compact('desas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $validated = $request->validate([
            'desa_id' => 'required|exists:wilayah_desa,kode_desa',
            'username' => 'required|string|max:150',
            'password' => 'required|string|min:6', // Virtual password input
            'jenis_vpn' => 'nullable|in:PPTP,L2TP,OpenVPN,WireGuard',
        ]);

        $desa = WilayahDesa::findOrFail($request->desa_id);
        
        // Check uniqueness
        if (VpnDesa::where('kode_desa', $desa->kode_desa)->exists()) {
             return back()->with('error', 'Desa ini sudah memiliki akun VPN.')->withInput();
        }
        
        $data = [
            'kode_desa' => $desa->kode_desa,
            'username' => $validated['username'],
            'password' => $validated['password'], // Storing as plain text per requirement
            'jenis_vpn' => $validated['jenis_vpn'] ?? 'OpenVPN',
        ];

        VpnDesa::create($data);

        return redirect()->route('vpn.index')
                         ->with('success', 'Akun VPN berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $vpn = VpnDesa::with('desa')->findOrFail($id);
        return view('vpn.show', compact('vpn'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $vpn = VpnDesa::with('desa')->findOrFail($id);
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        return view('vpn.edit', compact('vpn', 'desas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $vpn = VpnDesa::findOrFail($id);

        $validated = $request->validate([
            // 'desa_id' => 'required', // Cannot change desa
            'username' => 'required|string|max:150',
            'password' => 'nullable|string|min:6', // Optional update
            'jenis_vpn' => 'nullable|in:PPTP,L2TP,OpenVPN,WireGuard',
        ]);
        
        $data = [
            'username' => $validated['username'],
            'jenis_vpn' => $validated['jenis_vpn'] ?? $vpn->jenis_vpn,
        ];
        
        if ($request->filled('password')) {
            $data['password'] = $request->password; // Storing as plain text
        }

        $vpn->update($data);

        return redirect()->route('vpn.index')
                         ->with('success', 'Data VPN berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $vpn = VpnDesa::findOrFail($id);
        $vpn->delete();

        return redirect()->route('vpn.index')
                         ->with('success', 'Data VPN berhasil dihapus');
    }
}
