<table class="table table-hover table-bordered table-custom">
    <thead>
    <tr>
        <th class="table-title">
            {{ \Translate::get('pages::admin/main.list.title') }}
        </th>
        <th>
            {{ \Translate::get('pages::admin/main.list.languages') }}
        </th>
        <th>
            {{ \Translate::get('pages::admin/main.list.type') }}
        </th>
        <th>
            {{ \Translate::get('pages::admin/main.list.updated') }}
        </th>
        <th class="table-icon-cell table-actions"></th>
    </tr>
    </thead>
    <tbody>
    @foreach($list as $item)
        <tr>
            <td>
                <a href="#">
                    {{ $item['name']}}
                </a>
            </td>
            <td>
                ----
            </td>
            <td class="color-blue">
                ----
            </td>
            <td class="table-date">
                {{ $item['release']['date'] }} <i class="font-icon font-icon-clock"></i>
            </td>
            <td class="table-icon-cell">
            </td>
        </tr>
    @endforeach
    </tbody>
</table>