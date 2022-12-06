<?php

namespace Uchup07\Messages\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Uchup07\Messages\Models\Participant;
use Uchup07\Messages\Models\Thread;
class MessageController extends Controller
{
    protected $threadClass, $participantClass;

    public function __construct()
    {
        parent::__construct();

        $this->threadClass = config('laravel-messages.models.thread');
        $this->participantClass = config('laravel-messages.models.participant');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        if (request()->has('sent')) {
            $threads = $user->sent();
        } else {
            $threads = $user->received();
        }

        $threads = $threads->paginate(config('laravel-messages.paginate', 10));


        return view('laravel-messages::index', compact('threads'));
    }

    /**
     *
     */
    public function create()
    {
        return view('laravel-messages::create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required',
            'body' => 'required',
            'recipients' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $recipients = is_string($request->recipients) ? explode(',', $request->recipients) : json_decode($request->recipients);

            $thread = auth()->user()
                ->subject($request->subject)
                ->writes($request->body)
                ->to($recipients)
                ->send();

            DB::commit();

            return redirect()
                ->route(config('laravel-messages.route.name') . 'message.index')
                ->with('message', [
                    'type' => $thread ? 'success' : 'error',
                    'text' => $thread ? trans('laravel-messages::messages.thread.sent') : trans('laravel-messages::messages.thread.whoops'),
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $messages = $e->getMessage();
            Log::error($e->getTraceAsString());

            return redirect()
                ->route(config('laravel-messages.route.name') . 'message.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => $messages,
                ]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param Thread $thread
     *
     * @return \Illuminate\Http\Response
     */
    public function show($thread)
    {
        $threadClass = $this->threadClass;
        $thread = $threadClass::findOrFail($thread);
        $previous = $threadClass::where('id', '<', $thread->id)->latest()->first();
        $next = $threadClass::where('id', '>', $thread->id)->oldest()->first();

        $currentPage = 1;

        $threads = $threadClass::latest()->get();
        $totalThread = $threads->count();

        for($i=0;$i < $totalThread; $i++) {
            if($threads[$i]->id == $thread->id) {
                $currentPage = $currentPage + $i;
            }
        }

        $messages = $thread->messages()->get();

        $seen = $thread->participants()
            ->where('user_id', auth()->id())
            ->first();

        if ($seen && $seen->pivot) {
            $seen->pivot->seen_at = Carbon::now();
            $seen->pivot->save();
        } else {
            return abort(404);
        }

        return view('laravel-messages::show', compact('messages', 'thread','previous','next','totalThread','currentPage'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Thread                    $thread
     *
     * @return \Illuminate\Http\Response
     */
    public function reply(Request $request, $thread)
    {
        $threadClass = $this->threadClass;
        $thread = $threadClass::findOrFail($thread);

        $request->validate([
            'body' => 'required',
        ]);

        $message = '';

        try {
            DB::beginTransaction();

            $message = auth()->user()
                ->writes($request->body)
                ->reply($thread);

            DB::commit();

            return redirect()
                ->route(config('laravel-messages.route.name') . 'message.show', $thread)
                ->with('message', [
                    'type' => $message ? 'success' : 'error',
                    'text' => $message ? trans('laravel-messages::messages.message.sent') : trans('laravel-messages::messages.message.whoops'),
                ]);


        } catch (\Exception $e) {
            DB::rollBack();
            $messages = $e->getMessage();
            Log::error($e->getTraceAsString());

            return redirect()
                ->route(config('laravel-messages.route.name') . 'message.index')
                ->with('message', [
                    'type' => 'error',
                    'text' => $messages,
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Thread $thread
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($thread)
    {
        $threadClass = $this->threadClass;
        $thread = $threadClass::findOrFail($thread);

        $message = Participant::where('user_id', auth()->id())
            ->where('thread_id', $thread->id)
            ->firstOrFail();

        $deleted = $message->delete();

        return redirect()
            ->route(config('laravel-messages.route.name') . 'message.index')
            ->with('message', [
                'type' => $deleted ? 'success' : 'error',
                'text' => $deleted ? trans('laravel-messages::messages.thread.deleted') : trans('laravel-messages::messages.thread.whoops'),
            ]);
    }
}