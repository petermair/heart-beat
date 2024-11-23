<?php

namespace App\Http\Integrations\ThingsBoardHttp\Requests\Devices;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;
use Saloon\PaginationPlugin\CursorPaginator;

class DevicesHttpRequest extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ?int $pageSize = 10,
        protected ?string $page = null,
        protected ?string $textSearch = null,
        protected ?string $sortProperty = null,
        protected ?string $sortOrder = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/api/devices';
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

    public function createPaginator(): CursorPaginator
    {
        return new CursorPaginator(
            $this,
            $this->pageSize ?? 10,
            'page'
        );
    }
}
