<?php

namespace KwtSMS\Laravel\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use KwtSMS\Laravel\Models\KwtSmsLog;

class LogsController extends Controller
{
    public function index(): View
    {
        $logs = KwtSmsLog::query()
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('kwtsms::admin.logs', compact('logs'));
    }

    public function show(KwtSmsLog $log): View
    {
        return view('kwtsms::admin.logs-show', compact('log'));
    }

    public function clear(Request $request): RedirectResponse
    {
        // Require explicit confirmation to prevent accidental or automated mass deletion.
        if ($request->input('confirm') !== 'yes') {
            return redirect()->route('kwtsms.logs.index')->with('error', 'Confirmation required to clear logs.');
        }

        KwtSmsLog::query()->delete();

        return redirect()->route('kwtsms.logs.index')->with('success', __('kwtsms::kwtsms.logs_cleared'));
    }

    public function export(): Response
    {
        $logs = KwtSmsLog::query()->orderByDesc('created_at')->limit(10000)->get();

        // Note: the recipient column contains full phone numbers in international format.
        // This export is admin-only and should be treated as sensitive data.
        $csv = "ID,Recipient,Sender,Message,Status,Event Type,Is Test,Points Charged,Balance After,Error Code,Sent At,Created At\n";
        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->id,
                '"'.str_replace('"', '""', $log->recipient).'"',
                '"'.str_replace('"', '""', (string) $log->sender_id).'"',
                '"'.str_replace('"', '""', $log->message).'"',
                $log->status,
                (string) $log->event_type,
                $log->is_test ? '1' : '0',
                (string) $log->points_charged,
                (string) $log->balance_after,
                (string) $log->error_code,
                (string) $log->sent_at,
                (string) $log->created_at,
            ])."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="kwtsms-logs-'.now()->format('Y-m-d').'.csv"',
        ]);
    }
}
