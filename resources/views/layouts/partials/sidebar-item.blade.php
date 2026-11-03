<li>
    <a href="{{ $itemHref }}" class="menu-item group"
        :class="[
            isActive('{{ $item['path'] }}') ? 'menu-item-active' : 'menu-item-inactive',
            (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
            'xl:justify-center' : 'justify-start'
        ]">
        <span :class="isActive('{{ $item['path'] }}') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
            {!! \App\Helpers\MenuHelper::getIconSvg($item['icon']) !!}
        </span>
        <span
            x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
            class="menu-item-text flex min-w-0 items-center gap-2 truncate">
            {{ __($item['name']) }}
        </span>
    </a>
</li>
