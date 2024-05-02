<div class="modal fade" tabindex="-1" role="dialog" id="modal-chain-delete">
    <input type="hidden" name="chainId"/>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__('modals.deleteChain-title')}}</h4>
                <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    {{__('modals.deleteChain-body')}}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-mdb-dismiss="modal">
                    {{__('menu.abort')}}
                </button>
                <button type="button" class="btn btn-danger"
                        data-mdb-dismiss="modal"
                        onclick="TravelChain.destroy(document.querySelector('#modal-chain-delete input[name=\'chainId\']').value)"
                >
                    {{__('modals.delete-confirm')}}
                </button>
            </div>
        </div>
    </div>
</div>
