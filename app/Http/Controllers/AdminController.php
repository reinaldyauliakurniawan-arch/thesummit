<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExpeditionCard;

class AdminController extends Controller
{
    public function indexCards()
    {
        $cards = ExpeditionCard::orderBy('level')
            ->orderBy('kategori')
            ->paginate(20);

        return view('admin.cards', compact('cards'));
    }

    public function createCard()
    {
        return view('admin.cards-form', ['card' => null]);
    }

    public function storeCard(Request $request)
    {
        $data = $request->validate([
            'level'          => 'required|in:basecamp,camp,summit',
            'kategori'       => 'required|in:mindset,skillset',
            'tipe'           => 'required|in:netral,krisis',
            'teks_situasi'   => 'required|string',
            'opsi_a_teks'    => 'required|string',
            'opsi_a_mp'      => 'required|integer|between:-5,5',
            'opsi_a_sp'      => 'required|integer|between:-5,5',
            'opsi_a_tt'      => 'required|integer|between:-5,5',
            'opsi_a_extra'   => 'nullable|string',
            'opsi_b_teks'    => 'required|string',
            'opsi_b_mp'      => 'required|integer|between:-5,5',
            'opsi_b_sp'      => 'required|integer|between:-5,5',
            'opsi_b_tt'      => 'required|integer|between:-5,5',
            'opsi_b_extra'   => 'nullable|string',
            'dysfunction_tag' => 'nullable|in:absence_of_trust,fear_of_conflict,lack_of_commitment,avoidance_of_accountability,inattention_to_results',
        ]);

        ExpeditionCard::create($data);

        return redirect()
            ->route('admin.cards.index')
            ->with('success', 'Kartu ditambahkan.');
    }

    public function editCard(ExpeditionCard $card)
    {
        return view('admin.cards-form', compact('card'));
    }

    public function updateCard(Request $request, ExpeditionCard $card)
    {
        $data = $request->validate([
            'level'          => 'required|in:basecamp,camp,summit',
            'kategori'       => 'required|in:mindset,skillset',
            'tipe'           => 'required|in:netral,krisis',
            'teks_situasi'   => 'required|string',
            'opsi_a_teks'    => 'required|string',
            'opsi_a_mp'      => 'required|integer|between:-5,5',
            'opsi_a_sp'      => 'required|integer|between:-5,5',
            'opsi_a_tt'      => 'required|integer|between:-5,5',
            'opsi_a_extra'   => 'nullable|string',
            'opsi_b_teks'    => 'required|string',
            'opsi_b_mp'      => 'required|integer|between:-5,5',
            'opsi_b_sp'      => 'required|integer|between:-5,5',
            'opsi_b_tt'      => 'required|integer|between:-5,5',
            'opsi_b_extra'   => 'nullable|string',
            'dysfunction_tag' => 'nullable|in:absence_of_trust,fear_of_conflict,lack_of_commitment,avoidance_of_accountability,inattention_to_results',
        ]);

        $card->update($data);

        return redirect()
            ->route('admin.cards.index')
            ->with('success', 'Kartu diupdate.');
    }

    public function deleteCard(ExpeditionCard $card)
    {
        $card->delete();

        return redirect()
            ->route('admin.cards.index')
            ->with('success', 'Kartu dihapus.');
    }
}