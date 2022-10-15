<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MealsController extends Controller
{
    private function isAdmin()
    {
        return \App\Models\User::find(Auth::user()->id)->can('users');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        if ($this->isAdmin())
            $request->validate([
                'username' => 'nullable|string'
            ]);

        $perPage = request('perPage', 100000);

        $results = DB::table('meals')
            ->join('users', 'users.id', '=', 'meals.user_id')
            ->when($request->username, function ($query, $username) {
                $query->where('users.name', 'like', "%{$username}%");
            }, function ($query) {
                if (!$this->isAdmin())
                    $query->where('users.id', auth()->user()->id);
            })
            ->where('meals.time_eaten', '>=', $request->from)
            ->where('meals.time_eaten', '<=', $request->to)
            ->select('meals.*', 'users.name as user_name')
            ->orderBy('meals.time_eaten')
            ->paginate($perPage);

        if ($this->isAdmin()) {
            return [
                'items' => $results,
                'statistics' => $this->adminStatistics()
            ];
        } else {
            return [
                'items' => $results,
                'statistics' => $this->statistics()
            ];
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->isAdmin()) {
            $user_id = Auth::user()->id;
        } else {
            $request->validate([
                'user_id' => 'required|exists:App\Models\User,id',
            ]);
            $user_id = $request->user_id;
        }

        $request->validate([
            'name' => 'required|string',
            'calories' => 'required|numeric',
            'price' => 'required|numeric',
            'time_eaten' => 'required|date',
        ]);

        return Meal::create([
            'name' => $request->name,
            'calories' => $request->calories,
            'price' => $request->price,
            'time_eaten' => $request->time_eaten,
            'user_id' => $user_id
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function show(Meal $meal)
    {
        if (!$this->isAdmin() && $meal->user_id != Auth::user()->id) {
            abort(403);
        }

        return $meal;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Meal $meal)
    {
        if (!$this->isAdmin() && $meal->user_id != Auth::user()->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string',
            'calories' => 'required|numeric',
            'price' => 'required|numeric',
            'time_eaten' => 'required|date',
        ]);

        if ($this->isAdmin()) {
            $request->validate([
                'user_id' => 'required|exists:App\Models\User,id'
            ]);
        }

        $meal->update($request->all());

        return $meal;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Meal $meal)
    {
        $user_id = Auth::user()->id;
        if (!$this->isAdmin() && $meal->user_id != $user_id) {
            abort(403);
        }

        $meal->delete();

        return response(null, 204);
    }

    private function adminStatistics()
    {
        $lastWeekPerUser = DB::table('meals')
            ->where('time_eaten', '>=', Carbon::now()->subDays(7)->format('Y-m-d') . ' 00:00:00')
            ->where('time_eaten', '<=', Carbon::now()->format('Y-m-d') . ' 23:59:59')
            ->get()
            ->groupBy('user_id');

        $addedThisWeek = DB::table('meals')
            ->where('time_eaten', '>=', Carbon::now()->subDays(7)->format('Y-m-d') . ' 00:00:00')
            ->where('time_eaten', '<=', Carbon::now()->format('Y-m-d') . ' 23:59:59')
            ->count();

        $addedPrevWeek = DB::table('meals')
            ->where('time_eaten', '>=', Carbon::now()->subDays(14)->format('Y-m-d') . ' 00:00:00')
            ->where('time_eaten', '<=', Carbon::now()->subDays(7)->format('Y-m-d') . ' 23:59:59')
            ->count();

        $sum = 0;
        foreach ($lastWeekPerUser as $meals) {
            foreach ($meals as $meal) {
                $sum += $meal->calories;
            }
        }

        $sum = $sum / $lastWeekPerUser->count();

        return [
            'averageCalories' => $sum,
            'addedThisWeek' => $addedThisWeek,
            'addedPrevWeek' => $addedPrevWeek,
        ];
    }

    private function statistics()
    {
        $mealGroups = Meal::where('user_id', auth()->user()->id)->get()
            ->groupBy(function ($item) {
                return substr($item->time_eaten, 0, 7);
            });

        $overspentMonths = [];

        foreach ($mealGroups as $month => $meals) {
            $sum = 0;
            foreach ($meals as $meal) {
                $sum += $meal->price;
            }
            if ($sum > 1000) {
                $overspentMonths[] = $month;
            }
        }

        return [
            'overspentMonths' => $overspentMonths
        ];
    }
}
