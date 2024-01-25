<?php

namespace App\Http\Controllers\api\agent;

use App\Http\Controllers\Controller;
use App\Models\PropertyPayment;
use Illuminate\Http\Request;

class AgentTransactionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function successfulTransactions()
    {
        $properties = PropertyPayment::where(['agent_id' => auth()->user()->id, 'payment_status' => true])->get();
        if ($properties->count() > 0) {
            return response()->json(['transactions'=>$properties],200);
        } else {
            return response()->json(['message' => 'No Successful Transaction(s) found'], 404);
        }
    }

    public function pendingTransactions()
    {
        $properties = PropertyPayment::where(['agent_id' => auth()->user()->id, 'payment_status' => false])->get();
        if ($properties->count() > 0) {
            return response()->json(['transactions'=>$properties],200);
        } else {
            return response()->json(['message' => 'No Pending Transaction(s) found'], 404);
        }
    }

    public function allTransactions()
    {
        $properties = PropertyPayment::where(['agent_id' => auth()->user()->id])->get();
        if ($properties->count() > 0) {
            return response()->json(['transactions'=>$properties],200);
        } else {
            return response()->json(['message' => 'No Transaction(s) found'], 404);
        }
    }
}
