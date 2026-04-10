<?php

declare(strict_types=1);

namespace App\Http\Requests\Scolarite;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id' => ['required', 'exists:groups,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'formateur_id' => ['required', 'exists:users,id'],
            'start_time' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before:20:00'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time', 'before_or_equal:20:00'],
            'day_of_week' => ['required', 'integer', 'min:1', 'max:7'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            $dayOfWeek = $this->input('day_of_week');
            $roomId = $this->input('room_id');
            $formateurId = $this->input('formateur_id');

            // Vérification conflit salle
            $roomConflict = Schedule::where('room_id', $roomId)
                ->where('day_of_week', $dayOfWeek)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                })
                ->when($this->route('schedule'), function ($query) {
                    $query->where('id', '!=', $this->route('schedule')->id);
                })
                ->exists();

            if ($roomConflict) {
                $validator->errors()->add('room_id', 'Cette salle est déjà occupée sur ce créneau.');
            }

            // Vérification conflit formateur
            $formateurConflict = Schedule::where('formateur_id', $formateurId)
                ->where('day_of_week', $dayOfWeek)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                })
                ->when($this->route('schedule'), function ($query) {
                    $query->where('id', '!=', $this->route('schedule')->id);
                })
                ->exists();

            if ($formateurConflict) {
                $validator->errors()->add('formateur_id', 'Ce formateur est déjà réservé sur ce créneau.');
            }
        });
    }
}
