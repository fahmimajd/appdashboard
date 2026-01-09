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
        $query = VpnDesa::with('desa.kecamatan');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('username', 'like', "%{$search}%");
        }

        if ($request->has('desa_id') && $request->desa_id != 'all') {
            $query->where('kode_desa', function($q) use ($request) {
                return WilayahDesa::where('id', $request->desa_id)->value('kode_desa');
            });
        }
        
        $vpns = $query->paginate(15);
        $desas = WilayahDesa::orderBy('nama_desa')->get();

        return view('vpn.index', compact('vpns', 'desas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        return view('vpn.create', compact('desas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'desa_id' => 'required|exists:wilayah_desa,id',
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
            'password_hash' => bcrypt($validated['password']),
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
        $vpn = VpnDesa::with('desa')->findOrFail($id);
        return view('vpn.show', compact('vpn'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $vpn = VpnDesa::with('desa')->findOrFail($id);
        $desas = WilayahDesa::orderBy('nama_desa')->get();
        return view('vpn.edit', compact('vpn', 'desas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
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
            $data['password_hash'] = bcrypt($request->password);
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
        $vpn = VpnDesa::findOrFail($id);
        $vpn->delete();

        return redirect()->route('vpn.index')
                         ->with('success', 'Data VPN berhasil dihapus');
    }
}
