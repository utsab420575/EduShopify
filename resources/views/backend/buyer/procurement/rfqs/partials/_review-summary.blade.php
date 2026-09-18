{{-- Shared by step 4's Review card and the Preview modal — reads the same Alpine state either way. --}}
<dl class="divide-y divide-gray-100 text-sm">
    <div class="py-3 flex items-start justify-between gap-4">
        <dt class="text-gray-500 shrink-0">Title</dt>
        <dd class="font-medium text-gray-900 text-right" x-text="title || '—'"></dd>
    </div>
    <div class="py-3">
        <dt class="text-gray-500 mb-2">Items (<span x-text="items.length"></span>)</dt>
        <div class="space-y-2">
            <template x-for="(item, idx) in items" :key="idx">
                <div class="border border-gray-200 rounded-lg overflow-hidden text-left" x-data="{ reviewExpanded: false }">
                    {{-- Row header — click to expand full details (specs, description, files) --}}
                    <div class="flex items-center gap-3 p-2.5 cursor-pointer hover:bg-gray-50 transition-colors"
                         @click="reviewExpanded = !reviewExpanded">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 shrink-0 overflow-hidden flex items-center justify-center">
                            <template x-if="reviewThumbnail(item)">
                                <img :src="reviewThumbnail(item)" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!reviewThumbnail(item)">
                                <i class="fa-solid fa-box text-gray-300 text-sm"></i>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-sm font-medium text-gray-900 truncate" x-text="item.item_name || 'Untitled item'"></span>
                                <span x-show="item._mode === 'requirement' || item.is_requirement"
                                      class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-900 border border-indigo-200 shrink-0">
                                    Quotation Only
                                </span>
                            </div>
                            <p class="text-[11px] text-gray-400 truncate" x-text="categoryBadgeNames(item).join(', ')"></p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-semibold text-gray-900" x-text="item.quantity + (item.custom_unit ? ' ' + item.custom_unit : '')"></p>
                            <p class="text-[11px] text-gray-400" x-show="item.estimated_unit_price" x-text="'@ ' + item.estimated_unit_price"></p>
                        </div>
                        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200 shrink-0"
                           :class="reviewExpanded ? 'rotate-180' : ''"></i>
                    </div>

                    {{-- Expanded details --}}
                    <div x-show="reviewExpanded" x-cloak class="border-t border-gray-100 bg-gray-50/60 p-3 space-y-2.5 text-xs">
                        <p x-show="item.description" class="text-gray-600 leading-relaxed" x-text="item.description"></p>

                        <template x-if="(item._attrGroups && item._attrGroups.length > 0) || (item.custom_attributes && item.custom_attributes.length > 0)">
                            <div class="space-y-1">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Specifications</p>
                                <template x-for="group in (item._attrGroups || [])" :key="group.group_id">
                                    <template x-for="attr in group.attributes" :key="attr.id">
                                        <div x-show="getAttrDisplayValue(item, attr)" class="flex items-center justify-between gap-2 py-0.5">
                                            <span class="text-gray-500" x-text="attr.name"></span>
                                            <span class="text-gray-800 font-medium text-right" x-text="getAttrDisplayValue(item, attr)"></span>
                                        </div>
                                    </template>
                                </template>
                                <template x-for="(attr, aIdx) in (item.custom_attributes || [])" :key="aIdx">
                                    <div x-show="attr.name || attr.value" class="flex items-center justify-between gap-2 py-0.5">
                                        <span class="text-gray-500" x-text="attr.name"></span>
                                        <span class="text-gray-800 font-medium text-right" x-text="attr.value"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="item.attachments && item.attachments.length > 0">
                            <div class="space-y-1 pt-1.5 border-t border-gray-200">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">
                                    Reference Files (<span x-text="item.attachments.length"></span>)
                                </p>
                                <template x-for="att in item.attachments" :key="att.id">
                                    <a :href="att.url" target="_blank" @click.stop
                                       class="flex items-center gap-1.5 text-indigo-600 hover:underline">
                                        <i class="fa-solid fa-paperclip text-[10px] shrink-0"></i>
                                        <span x-text="att.name" class="truncate"></span>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <p x-show="!item.description && (!item._attrGroups || item._attrGroups.length === 0)
                                    && (!item.custom_attributes || item.custom_attributes.length === 0)
                                    && (!item.attachments || item.attachments.length === 0)"
                           class="text-gray-400 italic">No additional details for this item.</p>
                    </div>
                </div>
            </template>
        </div>
    </div>
    <div class="py-3 flex items-start justify-between gap-4">
        <dt class="text-gray-500 shrink-0">Quotation Deadline</dt>
        <dd class="font-medium text-gray-900 text-right" x-text="quotationDeadline || '—'"></dd>
    </div>
    <div class="py-3">
        <dt class="text-gray-500 mb-2">Delivery Address(es)</dt>
        <ul class="space-y-1 text-right">
            <li class="text-gray-900" x-text="deliveryAddress || 'Address 1: not specified'"></li>
            <template x-for="(addr, idx) in additionalAddresses" :key="idx">
                <li class="text-gray-900" x-text="'Address ' + (idx + 2) + ': ' + (addr.address || 'not specified')"></li>
            </template>
        </ul>
    </div>
    <div class="py-3 flex items-start justify-between gap-4">
        <dt class="text-gray-500 shrink-0">Suppliers</dt>
        <dd class="font-medium text-gray-900 text-right" x-text="activeSuppliersSummary()"></dd>
    </div>
</dl>
