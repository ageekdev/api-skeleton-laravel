<?php

namespace App\Providers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        Response::macro('success', function ($data = [], $status = 200, array $headers = [], $options = 0) {
            if (is_scalar($data)) {
                $data = ['data' => $data];
            } elseif ($data instanceof Arrayable) {
                $data = $data->toArray();
            } elseif ($data instanceof ResourceCollection) {
                $status = $data->response()->status();
                $data = $data->response()->getData(true);
            } elseif ($data instanceof JsonResource) {
                $status = $data->response()->status();
                $data = $data->response()->getData(true);
            }

            $default = ['success' => true, 'message' => 'Successfully!', 'status' => $status];

            $data = array_merge($default, $data);

            return Response::json($data, $status, $headers, $options);
        });

        Response::macro('error', function ($data = [], $status = 500, array $headers = [], $options = 0) {
            $default = ['success' => false, 'status' => $status, 'message' => 'Error!'];

            if (is_scalar($data)) {
                $data = ['data' => $data];
            }

            $data = array_merge($default, $data);

            return Response::json($data, $status, $headers, $options);
        });
    }
}
