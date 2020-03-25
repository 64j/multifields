<div id="[+id+]" class="mf-table row [+class+]" data-type="table" data-name="[+name+]" [+attr+]>
    [+value+]
    [+actions+]
    [+columns.html+]
    <div class="row m-0 col-12 p-0">
        <div class="mf-column-menu contextMenu">
            <div onclick="Multifields.elements.table.addColumn(event);">
                <i class="fa fa-plus fa-fw"></i> Add column
            </div>
            <div onclick="Multifields.elements.table.delColumn(event);">
                <i class="fa fa-minus fa-fw"></i> Delete column
            </div>
            [+types+]
        </div>
    </div>
    <div class="mf-items mf-items-table row [+items.class+]">
        [+items+]
    </div>
</div>