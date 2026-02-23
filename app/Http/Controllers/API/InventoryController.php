<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Inventories;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function get(Request $request, $id = null)
    {
        $params = $request->all();

        if ($id != null) {
            $res = Inventories::getById($id, $params, $request);
        } else if (isset($params['all']) && $params['all']) {
            $res = Inventories::getAllResult($params, $request);
        } else {
            $res = Inventories::getPaginatedResult($params, $request);
        }

        return $res;
    }

    public function post(Request $request)
    {
        $params = $request->all();
        return Inventories::createOrUpdate($params, $request->method(), $request);
    }

    public function put(Request $request, $id)
    {
        $params = $request->all();
        $params['id'] = $id;
        return Inventories::createOrUpdate($params, $request->method(), $request);
    }

    public function patch(Request $request, $id)
    {
        $params = $request->all();
        $params['id'] = $id;
        return Inventories::createOrUpdate($params, $request->method(), $request);
    }

    public function delete(Request $request, $id)
    {
        $params = $request->all();
        return Inventories::deleteById($id, $params, $request);
    }

    public function approve(Request $request, $id)
    {
        $params = $request->all();
        return Inventories::approveById($id, $params, $request);
    }

    public function datatables(Request $request)
    {
        $columns = [
            'inventories.id'
        ];

        $dataOrder = [];

        $limit = $request->length;
        $start = $request->start;

        foreach ($request->order as $row) {
            $nestedOrder['column'] = $columns[$row['column']];
            $nestedOrder['dir'] = $row['dir'];
            $dataOrder[] = $nestedOrder;
        }

        $order = $dataOrder;
        $dir = $request->order[0]['dir'];
        $search = $request->search['value'];
        $filter = $request->filter;

        $res = Inventories::datatables($start, $limit, $order, $dir, $search, $filter);

        $data = [];

        if (!empty($res['data'])) {
            foreach ($res['data'] as $row) {
                $nestedData = $row;
                $nestedData['action'] = '';
                $nestedData['action'] .= '<div class="actions">';
                if (\Illuminate\Support\Facades\Auth::user()->role === \App\Enums\Role::SUPER_ADMIN) {
                    $nestedData['action'] .= '<a href="#" class="btn btn-icon btn-warning" id="edit-data" data-id="' . $row['id'] . '"><i class="fa fa-pencil"></i></a>';
                    $nestedData['action'] .= '&nbsp;';
                    $nestedData['action'] .= '<a href="#" class="btn btn-icon btn-danger" id="delete-data" data-id="' . $row['id'] . '"><i class="fa fa-trash-o"></i></a>';
                }
                $nestedData['action'] .= '</div>';

                $data[] = $nestedData;
            }
        }

        $jsonData = [
            'draw' => intval($request->draw),
            'recordsTotal' => intval($res['totalData']),
            'recordsFiltered' => intval($res['totalFiltered']),
            'data' => $data,
            'order' => $order
        ];

        return json_encode($jsonData);
    }
}
