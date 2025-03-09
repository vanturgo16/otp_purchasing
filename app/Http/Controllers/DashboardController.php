<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrders;
use App\Models\PurchaseRequisitions;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Purchasing']);
       
    }
    public function index(){
        $totalPR = PurchaseRequisitions::count();
        $totalPRToday = PurchaseRequisitions::whereDate('created_at', today())->count();
        $totalPRReq = PurchaseRequisitions::whereIn('status', ['Request', 'Un Posted'])->count();
        $totalPRReqToday = PurchaseRequisitions::whereIn('status', ['Request', 'Un Posted'])->whereDate('created_at', today())->count();
        $totalPRPosted = PurchaseRequisitions::whereIn('status', ['Posted', 'Created PO'])->count();
        $totalPRPostedToday = PurchaseRequisitions::whereIn('status', ['Posted', 'Created PO'])->whereDate('created_at', today())->count();
        $totalPRClosed = PurchaseRequisitions::where('status', 'Closed')->count();
        $totalPRClosedToday = PurchaseRequisitions::where('status', 'Closed')->whereDate('created_at', today())->count();
        
        $totalPO = PurchaseOrders::count();
        $totalPOToday = PurchaseOrders::whereDate('created_at', today())->count();
        $totalPOReq = PurchaseOrders::whereIn('status', ['Request', 'Un Posted'])->count();
        $totalPOReqToday = PurchaseOrders::whereIn('status', ['Request', 'Un Posted'])->whereDate('created_at', today())->count();
        $totalPOPosted = PurchaseOrders::where('status', 'Posted')->count();
        $totalPOPostedToday = PurchaseOrders::where('status', 'Posted')->whereDate('created_at', today())->count();
        $totalPOClosed = PurchaseOrders::where('status', 'Closed')->count();
        $totalPOClosedToday = PurchaseOrders::where('status', 'Closed')->whereDate('created_at', today())->count();

        return view('dashboard.index', compact(
            'totalPR', 'totalPRToday', 'totalPRReq', 'totalPRReqToday', 'totalPRPosted', 'totalPRPostedToday', 'totalPRClosed', 'totalPRClosedToday',
            'totalPO', 'totalPOToday', 'totalPOReq', 'totalPOReqToday', 'totalPOPosted', 'totalPOPostedToday', 'totalPOClosed', 'totalPOClosedToday'
        ));
    }
}
