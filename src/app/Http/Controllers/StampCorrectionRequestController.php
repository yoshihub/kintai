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

        $requestsQuery = StampCorrectionRequest::with(['attendance', 'user']);

        // 管理者の場合は全ユーザーの申請を表示、一般ユーザーの場合は自分の申請のみ
        if (Auth::guard('admin')->check()) {
            // 管理者: 全ユーザーの申請を表示
            // クエリに追加条件なし
        } else {
            // 一般ユーザー: 自分の申請のみ
            $requestsQuery->where('user_id', Auth::id());
        }

        // ステータスによるフィルタリング
        if ($status === 'pending') {
            $requestsQuery->where('status', 'pending');
        } elseif ($status === 'approved') {
            $requestsQuery->where('status', 'approved');
        }

        $requests = $requestsQuery->orderBy('created_at', 'desc')->get();

        // 管理者かどうかの判定を渡す
        $isAdmin = Auth::guard('admin')->check();

        return view('stamp_correction_request.list', compact('requests', 'status', 'isAdmin'));
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

    /**
     * 修正申請承認画面を表示（管理者用）
     */
    public function showApproval($attendance_correct_request)
    {
        $correctionRequest = StampCorrectionRequest::with(['attendance', 'user'])->findOrFail($attendance_correct_request);

        return view('admin.stamp_correction_request.approve', compact('correctionRequest'));
    }

    /**
     * 修正申請を承認（管理者用）
     */
    public function approve($attendance_correct_request)
    {
        $correctionRequest = StampCorrectionRequest::with('attendance')->findOrFail($attendance_correct_request);

        // 勤怠データを修正申請内容で更新
        $attendance = $correctionRequest->attendance;
        $attendance->clock_in = $correctionRequest->start_time;
        $attendance->clock_out = $correctionRequest->end_time;
        $attendance->save();

        // 既存の休憩データを削除
        $attendance->breaks()->delete();

        // 修正申請の休憩データを保存
        if ($correctionRequest->breaks) {
            foreach ($correctionRequest->breaks as $breakData) {
                if (!empty($breakData['break_start']) && !empty($breakData['break_end'])) {
                    $attendance->breaks()->create([
                        'break_start' => $breakData['break_start'],
                        'break_end' => $breakData['break_end'],
                    ]);
                }
            }
        }

        // 修正申請のステータスを承認済みに更新
        $correctionRequest->update(['status' => 'approved']);

        return redirect()->route('stamp_correction_request.list')
            ->with('success', '修正申請を承認しました');
    }
}
