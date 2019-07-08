<table class="table table-hover table-bordered table-custom">
    <thead>
    <tr>
        <th>
            {{ \Translate::get('module_packagesmanager::admin/main.title') }}
        </th>
        <th>
            {{ \Translate::get('module_packagesmanager::admin/main.description') }}
        </th>
        <th>
            {{ \Translate::get('module_packagesmanager::admin/main.vendor') }}
        </th>
        <th>
            {{ \Translate::get('module_packagesmanager::admin/main.version') }}
        </th>
        <th>
            {{ \Translate::get('module_packagesmanager::admin/main.installed') }}
        </th>
        <th>
            {{ \Translate::get('module_packagesmanager::admin/main.enabled') }}
        </th>
        <th>
            {{ \Translate::get('module_packagesmanager::admin/main.updated') }}
        </th>
        <!--<th class="table-icon-cell table-actions"></th>-->
    </tr>
    </thead>
    <tbody>
    @foreach($list as $item)
        <tr>
            <td>
                <a href="#">
                    {{ $item['module_info']['name']}}
                </a>
            </td>
            <td>
                {{ $item['description']}}
            </td>
            <td>
                <a href="{{ $item['url']}}" target="_blank">{{ $item['vendor']}}</a>
            </td>
            <td class="color-blue">
                <a href="{{ $item['release']['url'] }}" target="_blank">{{ $item['release']['name']}}</a>
            </td>
            <td class="color-blue">
                @if ($item['installed'])
                    <a data-delete="{{ \Translate::get('module_packagesmanager::admin/main.action_module_remove') }}" href="{{ route('admin.packages.change_install', ['module' => $item['module_info']['name']]) }}"><i class="font-icon font-icon-ok"></i></a>
                @else
                    <a data-delete="{{ \Translate::get('module_packagesmanager::admin/main.action_module_install') }}" href="{{ route('admin.packages.change_install', ['module' => $item['module_info']['name']]) }}"><i class="font-icon font-icon-del"></i></a>
                @endif
            </td>
            <td class="color-blue">
                @if ($item['enabled'])
                    <a data-delete="{{ \Translate::get('module_packagesmanager::admin/main.action_module_disable') }}" href="{{ route('admin.packages.change_active', ['module' => $item['module_info']['name']]) }}"><i class="font-icon font-icon-ok"></i></a>
                @else
                    <a data-delete="{{ \Translate::get('module_packagesmanager::admin/main.action_module_enable') }}" href="{{ route('admin.packages.change_active', ['module' => $item['module_info']['name']]) }}"><i class="font-icon font-icon-del"></i></a>
                @endif
            </td>
            <td class="table-date">
                {{ date('Y-m-d', $item['release']['date']) }} <i class="font-icon font-icon-clock"></i>
            </td>
            <!--<td class="table-icon-cell"></td>-->
        </tr>
    @endforeach
    </tbody>
</table>