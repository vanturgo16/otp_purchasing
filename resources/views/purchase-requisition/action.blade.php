<a href="{{ route('pr.print', ['lang' => 'en', 'id' => encrypt($data->id)]) }}" class="btn btn-sm btn-info waves-effect waves-light my-half">
    <i class="bx bx-printer" title="Print in English"></i>
</a>
<a href="{{ route('pr.print', ['lang' => 'idn', 'id' => encrypt($data->id)]) }}" class="btn btn-sm btn-success waves-effect waves-light my-half">
    <i class="bx bx-printer" title="Cetak dalam Indonesia"></i>
</a>
@if(in_array($data->status, ['Request', 'Un Posted']))
    <button class="btn btn-sm btn-danger my-half" data-bs-toggle="modal" data-bs-target="#delete{{ $data->id }}">
        <i class="bx bx-trash-alt" title="Hapus Data"></i>
    </button>
    <a href="{{ route('pr.edit', encrypt($data->id)) }}" class="btn btn-sm btn-primary waves-effect waves-light my-half">
        <i class="bx bx-edit-alt" title="Edit Data"></i>
    </a>
    @if($data->count == 0)
        <button class="btn btn-sm btn-success my-half" data-bs-toggle="modal" data-bs-target="#postedYet{{ $data->id }}">
            <i class="bx bx-paper-plane" title="Posted PR"></i>
        </button>
    @else
        <button class="btn btn-sm btn-success my-half" data-bs-toggle="modal" data-bs-target="#posted{{ $data->id }}">
            <i class="bx bx-paper-plane" title="Posted PR"></i>
        </button>
    @endif
    {{-- Modal Posted --}}
    <div class="modal fade" id="posted{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-top" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Posted</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pr.posted', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="text-center">
                            Apakah Anda Yakin Untuk <b>Posted</b> Data?
                            <br><b>"{{ $data->request_number }}"</b>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success waves-effect btn-label waves-light">
                            <i class="mdi mdi-send-check label-icon"></i>Posted
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal PostedYet --}}
    <div class="modal fade" id="postedYet{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-top" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Posted</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center">
                        Tidak Dapat Melakukan Posted <br>
                        Product Dalam Purchase Requisition <b>"0"</b>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal Delete --}}
    <div class="modal fade" id="delete{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-top" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pr.delete', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="text-center">
                            Apakah Anda Yakin Untuk <b>Menghapus</b> Data?
                            <br><b>"{{ $data->request_number }}"</b>
                            @if($data->po_number)
                                <br>
                                <br>
                                Purchase Order dengan nomor PR akan terhapus
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger waves-effect btn-label waves-light">
                            <i class="mdi mdi-delete-alert label-icon"></i>Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@if(in_array($data->status, ['Posted', 'Created PO']))
    @can('PPIC_unposted')
        <button class="btn btn-sm btn-secondary my-half" data-bs-toggle="modal" data-bs-target="#unposted{{ $data->id }}">
            <i class="mdi mdi-arrow-left-top-bold" title="Un Posted" >Un-Posted</i>
        </button>
        {{-- Modal Un-Posted --}}
        <div class="modal fade" id="unposted{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-top" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Un-Posted</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('pr.unposted', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="text-center">
                                Apakah Anda Yakin Untuk <b>Un-Posted</b> Data?
                                <br><b>"{{ $data->request_number }}"</b>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-secondary waves-effect btn-label waves-light">
                                <i class="mdi mdi-arrow-left-top-bold label-icon"></i>Un-Posted
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endif

<script>
    $(document).on('click', '[data-bs-toggle="modal"]', function(e) {
        e.preventDefault();
        let targetModal = $(this).attr('data-bs-target'); 
        // Ensure modal is moved outside the sticky column before opening
        $(targetModal).appendTo('body').modal('show');
    });
    $(document).on('submit', 'form', function () {
        let btn = $(this).find('button[type="submit"]');
        if (!$(this).valid()) return false;
        btn.prop("disabled", true).html('<i class="mdi mdi-loading mdi-spin label-icon"></i> Please Wait...');
        return true;
    });
</script>