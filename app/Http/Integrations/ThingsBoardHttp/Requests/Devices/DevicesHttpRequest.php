<?php

namespace App\Http\Integrations\ThingsBoardHttp\Requests\Devices;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\HasPagination;
use Saloon\PaginationPlugin\AbstractPaginator;
use Saloon\PaginationPlugin\PagedPaginator;

class DevicesHttpRequest extends Request implements HasPagination
{
    protected Method $method = Method::GET;

    public function __construct(
        protected int $pageSize = 10,
        protected int $page = 0,
        protected ?string $textSearch = null,
        protected ?string $sortProperty = null,
        protected ?string $sortOrder = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/api/tenant/devices';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'pageSize' => $this->pageSize,
            'page' => $this->page,
            'textSearch' => $this->textSearch,
            'sortProperty' => $this->sortProperty,
            'sortOrder' => $this->sortOrder,
        ]);
    }

    public function hasNextPage(Response $response): ?bool
    {
        $data = $response->json();
        return isset($data['hasNext']) ? $data['hasNext'] : false;
    }

    public function nextPageData(Response $response): array
    {
        return [
            'page' => $this->page + 1,
            'pageSize' => $this->pageSize,
        ];
    }

    public function paginate(Request $request): PagedPaginator
    {
        return new class(connector: $this, request: $request) extends PagedPaginator
        {
            protected function isLastPage(Response $response): bool
            {
                $data = $response->json();
                return !($data['hasNext'] ?? false);
            }
            
            protected function getPageItems(Response $response, Request $request): array
            {
                return $response->json('data');
            }
        };
    }    
}
