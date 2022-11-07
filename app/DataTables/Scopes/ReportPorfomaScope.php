<?php

namespace App\DataTables\Scopes;

use Illuminate\Http\Request;
use Yajra\DataTables\Contracts\DataTableScope;

use function GuzzleHttp\Promise\all;

class ReportPorfomaScope implements DataTableScope
{
    /**
     * Apply a query scope.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request  = $request;
    }

    public function apply($query)
    {
        // return $query->where('id', 1);
        $filters =  [
            'cupkg_status',
            'inv_status',
            'inv_start',
            'inv_post',
            'bulan',
            'tidak_terkirim'
        ];
        //dd($this->request->input('inv_status'));

        foreach ($filters as $field) {
            if ($this->request->has($field)) {
                if ($this->request->get($field) !== null) {
                    if ($field == 'inv_start') {
                        $exxplode = explode(' s/d ', $this->request->get($field));
                        //dd($exxplode);
                        //$query->where($field, '>=', date('Y-m-d',strtotime($this->request->get($exxplode[0]),)));   
                        //$query->whereBetween($field,  [$this->request->get($exxplode[0]),$this->request->get($exxplode[1])]);   
                        $query->whereRaw("(inv_start >= '" . $exxplode[0] . "' and inv_start <= '" . $exxplode[1] . "')");
                    } elseif ($field == 'tidak_terkirim') {
                        if ($this->request->get($field) == 0) {
                            $query->whereRaw("(wa_sent_number not like '+62%' AND wa_sent_number not like '62%' )");
                        }
                    } elseif ($field == 'bulan') {
                        //dd($this->request->get($field));
                        $query->whereRaw("(MONTH(inv_start) = '" . $this->request->get($field) . "' )");
                    } else {
                        
                        $query->where($field, '=', $this->request->get($field));
                    }
                }
                
            }
        }
     

        return $query;
    }
}
