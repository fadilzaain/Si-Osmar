@props(['id', 'columns' => []])

<div class="card-base data-table-wrapper">
    <table id="{{ $id }}" class="table" data-datatable style="width:100%">
        <thead>
            <tr>
                @foreach ($columns as $col)
                    <th>{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>