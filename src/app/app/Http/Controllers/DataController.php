<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;


class DataController extends Controller
{


    public array $fileData = [];

    public array $statusCodes = [
        "authorised" => [1, 100],
        "decline" => [2, 200],
        "refunded" => [3, 300],
    ];


    public function index()
    {
        $data = $this->readFilesData();
        $data = collect($data)
            ->when(request('provider'),
                function ($data) {
                    return $this->withProvider();
                })
            ->when(request('statusCode'),
                function ($data) {
                    return $this->withStatus($data);
                })
            ->when(request('balanceMin') && request('balanceMax'),
                function ($data) {
                    return $this->withBalance($data);
                })
            ->when(request('currency'),
                function ($data) {
                    return $this->withCurrency($data);
                });

        Cache::put('data', $data);
        return Cache::get('data', $data);
    }

    /**
     * =======> You can add any files under app/storage/json folder, and it will be automatically loaded <=======
     *
     * @return array
     */
    private function readFilesData(): array
    {
        $path = storage_path('json');
        $files = File::allFiles($path);
        $data = [];
        foreach ($files as $file) {
            $dataFromFile = json_decode(file_get_contents($file), true);
            $this->fileData[$file->getFilenameWithoutExtension()] = $dataFromFile;
            $data = array_merge($data, $dataFromFile);
        }
        return $data;
    }

    /**
     * @return Collection
     */
    private function withProvider(): Collection
    {
        if (isset($this->fileData[request('provider')])) {
            return collect($this->fileData[request('provider')]);
        }
        return abort(404, "Provider Not found");
    }

    /**
     * @param $data
     * @return Collection
     */
    private function withStatus($data): Collection
    {
       if (isset($this->statusCodes[request('statusCode')])) {
           $groupOne = $data->whereIn('statusCode', $this->statusCodes[request('statusCode')]);
           $groupTwo = $data->whereIn('status', $this->statusCodes[request('statusCode')]);
           $data = $groupOne->merge($groupTwo);

           return collect($data);
       }
        return abort(404, "Status Not Valid");
    }

    /**
     * @param $data
     * @return Collection
     */
    private function withBalance($data): Collection
    {
        $groupOne = $data->whereBetween('balance', [request('balanceMin'), request('balanceMax')]);
        $groupTwo = $data->whereBetween('parentAmount', [request('balanceMin'), request('balanceMax')]);
        $data = $groupOne->merge($groupTwo);

        return collect($data);
    }

    /**
     * @param $data
     * @return Collection
     */
    private function withCurrency($data): Collection
    {
        return collect($data)->where('currency', request('currency'));
    }


}
