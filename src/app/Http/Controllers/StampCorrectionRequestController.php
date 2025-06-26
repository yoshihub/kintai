<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use App\Http\Requests\StoreStampCorrectionRequest;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    /**
     * 修正申請一覧画面を表示
     */
    public function list(Request $request)
    {
        $status = $request->get('status', 'pending');

        $requestsQuery = StampCorrectionRequest::with(['attendance', 'user'])
            ->where('user_id', Auth::id());

        // ステータスによるフィルタリング
        if ($status === 'pending') {
            $requestsQuery->where('status', 'pending');
        } elseif ($status === 'approved') {
            $requestsQuery->where('status', 'approved');
        }

        $requests = $requestsQuery->orderBy('created_at', 'desc')->get();

        return view('stamp_correction_request.list', compact('requests', 'status'));
    }

    /**
     * 修正申請を作成
     */
    public function store(StoreStampCorrectionRequest $request)
    {
        $validated = $request->validated();

        // 対象勤怠レコードを取得（自分のもののみ）
        $attendance = Attendance::where('user_id', Auth::id())
            ->findOrFail($validated['attendance_id']);

        // すでに申請中かチェック
        $existingRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingRequest) {
            return back()->withErrors(['message' => 'この勤怠記録は既に修正申請中です。']);
        }

        // 修正申請を作成
        StampCorrectionRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'breaks' => $validated['breaks'] ?? null,
            'note' => $validated['note'],
            'status' => 'pending'
        ]);

        return redirect()->route('stamp_correction_request.list')
            ->with('success', '修正申請を提出しました');
    }
}
