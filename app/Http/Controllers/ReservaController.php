<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use App\Models\ReservationSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    private function getDaySlotsJson(ReservationSetting $settings): array
    {
        $data = [];
        foreach (range(1, 7) as $day) {
            $daySettings = $settings->getDaySettings($day);
            $groups = $settings->getTimeSlotGroups($day);
            $allSlots = $settings->generateTimeSlots($day);
            $data[$day] = [
                'activo' => $daySettings && $daySettings->activo,
                'groups' => $groups,
                'slots' => $allSlots,
            ];
        }

        return $data;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isUser()) {
            return redirect()->route('home');
        }

        $tab = $request->get('tab', 'proximas');
        if (! in_array($tab, ['proximas', 'historial'])) {
            $tab = 'proximas';
        }

        $base = ($user->isSuperAdmin() || $user->hasPermission('admin.reservas'))
            ? Reserva::with(['user', 'mesa'])
            : Reserva::where('user_id', $user->id)->with('mesa');

        $proximasCount = (clone $base)->where('fecha_hora', '>=', now())->count();
        $historialCount = (clone $base)
            ->where('fecha_hora', '<', now())
            ->where('fecha_hora', '>=', now()->subMonth())
            ->count();

        if ($tab === 'historial') {
            $reservas = (clone $base)
                ->where('fecha_hora', '<', now())
                ->where('fecha_hora', '>=', now()->subMonth())
                ->orderByDesc('fecha_hora')
                ->get();
        } else {
            $reservas = (clone $base)
                ->where('fecha_hora', '>=', now())
                ->orderBy('fecha_hora')
                ->get();
        }

        $grouped = $reservas->groupBy(fn ($r) => Carbon::createFromFormat('d-m-Y H:i', $r->fecha_hora)->locale('es')->isoFormat('dddd DD [de] MMMM [de] YYYY'));

        return view('reservas.index', compact('reservas', 'grouped', 'tab', 'proximasCount', 'historialCount'));
    }

    public function updateStatus(Request $request, Reserva $reserva)
    {
        if (auth()->id() !== $reserva->user_id && ! auth()->user()->isSuperAdmin() && ! auth()->user()->hasPermission('admin.reservas')) {
            abort(403);
        }

        $request->validate([
            'estado' => 'required|in:pendiente,confirmada,completada,cancelada',
        ]);

        $reserva->update(['estado' => $request->estado]);

        return redirect()->back()
            ->with('success', 'Reserva actualizada exitosamente.');
    }

    public function create()
    {
        if (auth()->user()->isUser()) {
            return redirect()->route('home');
        }

        $mesas = auth()->user()->isStaff()
            ? Mesa::where('disponible', true)->get()
            : collect();

        $reservationSettings = ReservationSetting::getInstance();
        $daySlotsJson = $this->getDaySlotsJson($reservationSettings);

        return view('reservas.create', compact('mesas', 'reservationSettings', 'daySlotsJson'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'mesa_id' => 'nullable|exists:mesas,id',
            'fecha' => 'required|date_format:Y-m-d|after_or_equal:today',
            'hora' => 'required|date_format:H:i',
            'numero_personas' => 'required|integer|min:1',
            'observaciones' => 'nullable|string|max:255',
        ];

        $messages = [];

        if ($request->mesa_id) {
            $rules['mesa_id'] = 'required|exists:mesas,id';
            $messages['mesa_id.required'] = 'Debes seleccionar una mesa';
        }

        $request->validate($rules, $messages);

        $settings = ReservationSetting::getInstance();

        $dow = (int) date('N', strtotime($request->fecha));
        if (! $settings->isDiaActivo($dow)) {
            return back()->withInput()->withErrors(['fecha' => 'Este día no está habilitado para reservas.']);
        }

        $slots = $settings->generateTimeSlots($dow);
        if (! in_array($request->hora, $slots)) {
            return back()->withInput()->withErrors(['hora' => 'La hora seleccionada no está disponible.']);
        }

        if ($request->numero_personas > $settings->max_personas) {
            return back()->withInput()->withErrors(['numero_personas' => 'Máximo '.$settings->max_personas.' personas por reserva.']);
        }

        return DB::transaction(function () use ($request, $user) {
            if ($user->isUser()) {
                $existing = Reserva::where('user_id', $user->id)
                    ->whereIn('estado', ['pendiente', 'confirmada'])
                    ->where('fecha_hora', '>=', now())
                    ->lockForUpdate()
                    ->exists();
                if ($existing) {
                    return back()->withInput()->withErrors([
                        'fecha_hora' => 'Ya tienes una reserva activa. Completa o cancela esa reserva antes de crear una nueva.',
                    ]);
                }
            }

            $fecha_hora = $request->fecha.' '.$request->hora;
            $fecha_hora_formatted = date('d-m-Y H:i', strtotime($fecha_hora));

            if ($request->mesa_id) {
                $mesa = Mesa::find($request->mesa_id);
                if (! $mesa || ! $mesa->disponible) {
                    return back()->withInput()->withErrors(['mesa_id' => 'La mesa seleccionada ya no está disponible.']);
                }

                $conflict = Reserva::where('mesa_id', $request->mesa_id)
                    ->where('fecha_hora', $fecha_hora_formatted)
                    ->whereIn('estado', ['pendiente', 'confirmada'])
                    ->exists();
                if ($conflict) {
                    return back()->withInput()->withErrors(['mesa_id' => 'La mesa ya está reservada para ese horario.']);
                }
            }

            $reserva = Reserva::create([
                'user_id' => auth()->id(),
                'mesa_id' => $request->mesa_id,
                'fecha_hora' => $fecha_hora_formatted,
                'numero_personas' => $request->numero_personas,
                'observaciones' => $request->observaciones,
                'estado' => 'pendiente',
            ]);

            if ($request->input('redirect_to') === 'home') {
                return redirect()->route('home')
                    ->with('success', 'Reserva creada exitosamente.')
                    ->with('nueva_reserva', [
                        'fecha' => $request->fecha,
                        'hora' => $request->hora,
                        'personas' => $request->numero_personas,
                        'observaciones' => $request->observaciones,
                    ]);
            }

            return redirect()->route('reservas.index')
                ->with('success', 'Reserva creada exitosamente.');
        });
    }

    public function edit(Reserva $reserva)
    {
        if (auth()->user()->isUser()) {
            return redirect()->route('home');
        }

        if (auth()->id() !== $reserva->user_id && ! auth()->user()->isSuperAdmin() && ! auth()->user()->hasPermission('admin.reservas')) {
            abort(403);
        }

        $mesas = auth()->user()->isStaff()
            ? Mesa::where('disponible', true)->orWhere('id', $reserva->mesa_id)->get()
            : collect();

        $reservationSettings = ReservationSetting::getInstance();
        $daySlotsJson = $this->getDaySlotsJson($reservationSettings);

        return view('reservas.edit', compact('reserva', 'mesas', 'reservationSettings', 'daySlotsJson'));
    }

    public function update(Request $request, Reserva $reserva)
    {
        if (auth()->id() !== $reserva->user_id && ! auth()->user()->isSuperAdmin() && ! auth()->user()->hasPermission('admin.reservas')) {
            abort(403);
        }

        $rules = [
            'mesa_id' => 'nullable|exists:mesas,id',
            'fecha' => 'required|date_format:Y-m-d|after_or_equal:today',
            'hora' => 'required|date_format:H:i',
            'numero_personas' => 'required|integer|min:1',
            'observaciones' => 'nullable|string|max:255',
            'estado' => 'required|in:pendiente,confirmada,completada,cancelada',
        ];

        $messages = [];

        if ($request->mesa_id) {
            $rules['mesa_id'] = 'required|exists:mesas,id';
            $messages['mesa_id.required'] = 'Debes seleccionar una mesa';
        }

        $request->validate($rules, $messages);

        $settings = ReservationSetting::getInstance();

        $dow = (int) date('N', strtotime($request->fecha));
        if (! $settings->isDiaActivo($dow)) {
            return back()->withInput()->withErrors(['fecha' => 'Este día no está habilitado para reservas.']);
        }

        $slots = $settings->generateTimeSlots($dow);
        if (! in_array($request->hora, $slots)) {
            return back()->withInput()->withErrors(['hora' => 'La hora seleccionada no está disponible.']);
        }

        if ($request->numero_personas > $settings->max_personas) {
            return back()->withInput()->withErrors(['numero_personas' => 'Máximo '.$settings->max_personas.' personas por reserva.']);
        }

        $fecha_hora = $request->fecha.' '.$request->hora;
        $fecha_hora_formatted = date('d-m-Y H:i', strtotime($fecha_hora));

        if ($request->mesa_id) {
            $mesa = Mesa::find($request->mesa_id);
            if (! $mesa || (! $mesa->disponible && $mesa->id !== $reserva->mesa_id)) {
                return back()->withInput()->withErrors(['mesa_id' => 'La mesa seleccionada ya no está disponible.']);
            }

            $conflict = Reserva::where('mesa_id', $request->mesa_id)
                ->where('fecha_hora', $fecha_hora_formatted)
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->where('id', '!=', $reserva->id)
                ->exists();
            if ($conflict) {
                return back()->withInput()->withErrors(['mesa_id' => 'La mesa ya está reservada para ese horario.']);
            }
        }

        $reserva->update([
            'mesa_id' => $request->mesa_id,
            'fecha_hora' => $fecha_hora_formatted,
            'numero_personas' => $request->numero_personas,
            'observaciones' => $request->observaciones,
            'estado' => $request->estado,
        ]);

        if ($request->input('redirect_to') === 'home') {
            return redirect()->route('home')->with('success', 'Reserva actualizada exitosamente.');
        }

        return redirect()->route('reservas.index')
            ->with('success', 'Reserva actualizada exitosamente.');
    }

    public function destroy(Request $request, Reserva $reserva)
    {
        if (auth()->id() !== $reserva->user_id && ! auth()->user()->isSuperAdmin() && ! auth()->user()->hasPermission('admin.reservas')) {
            abort(403);
        }

        $reserva->delete();

        if ($request->input('redirect_to') === 'home') {
            return redirect()->route('home')->with('success', 'Reserva eliminada exitosamente.');
        }

        return redirect()->route('reservas.index')
            ->with('success', 'Reserva eliminada exitosamente.');
    }
}
