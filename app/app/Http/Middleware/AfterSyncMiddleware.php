<?php

namespace App\Http\Middleware;

use App\Notifications;
use App\Variables;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\JWTAuth;

class AfterSyncMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        error_log(__METHOD__ );
        syslog(LOG_NOTICE,__METHOD__);
        return $next($request);
    }

    public function terminate($request, $response) {
        $modelNotifications = new Notifications();
        $modelVariables = new Variables();
        error_log(__METHOD__ );
        syslog(LOG_NOTICE,__METHOD__);
        $jobid = $request->get('jobid');
        $uid = $request->get('uid');
        $aMalformedItems = $request->get('malformedItems');
        $lastId = $modelVariables->getVar($modelVariables::VAR_LASTID_MALFORMED_INV_BARCODE, 0);
        $newLastId = $lastId;

        if (empty($aMalformedItems)) {
            return;
        }

        $modelInventuren = new Inventuren();
        $inventurData = $modelInventuren->getById($jobid);
        $inventurTitle = $inventurData['Titel'];

        $aMalformedBaseData = [];
        foreach($aMalformedItems as $_item) {
            if (!empty($_item['log_id']) && $_item['log_id'] > $newLastId) {
                $newLastId = $_item['log_id'];
            }
            $aBaseData = $modelNotifications->getInventarBaseData($_item);
            if ($aBaseData) {
                $aMalformedBaseData[] = $aBaseData;
            }
        }
        $table = $modelNotifications->getMalformedBarcodesAsTable($aMalformedBaseData);

        $message = "<h3>Kuerzlich aufgenommene neue Barcodes mit auffaelliger Struktur</h3>\n";
        $message.= $table;

        $insertId = $modelNotifications->insertEntity([
            'jobid' => $jobid,
            'type' => 'NEW_INV_CODE',
            'uid' => $uid,
            'notifications' => json_encode($this->aMalformedInvBarcodes),
            'subject' => $inventurTitle . ': Auffaellige neue Barcodes',
            'message' => $message,
            'process_status' => 0
        ], true);

        if ($newLastId != $lastId) {
            $modelVariables->setVar($modelVariables::VAR_LASTID_MALFORMED_INV_BARCODE, $newLastId);
        }

        $modelNotifications->sendBarcodeAlert($insertId);
    }
}
