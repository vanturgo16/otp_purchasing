@if(in_array($data->status, ['Request', 'Un Posted']))
    <button class="btn btn-sm btn-danger my-half" data-bs-toggle="modal" data-bs-target="#delete{{ $data->id }}">
        <i class="bx bx-trash-alt" title="Hapus Data"></i>
    </button>
    <a href="{{ route('pr.edit', encrypt($data->id)) }}" class="btn btn-sm btn-info waves-effect waves-light my-half">
        <i class="bx bx-edit-alt" title="Edit Data"></i>
    </a>
    <button class="btn btn-sm btn-success my-half" data-bs-toggle="modal" data-bs-target="#posted{{ $data->id }}">
        <i class="bx bx-paper-plane" title="Posted PR"></i>
    </button>
    {{-- Modal Delete --}}
    <div class="modal fade" id="delete{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-top" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pr.delete', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data" id="formDelete{{ $data->id }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="text-center">
                            Apakah Anda Yakin Untuk <b>Menghapus</b> Data?
                            <br><b>"{{ $data->request_number }}"</b>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger waves-effect btn-label waves-light" id="btnFormDelete{{ $data->id }}">
                            <i class="mdi mdi-delete-alert label-icon"></i>Delete
                        </button>
                    </div>
                </form>
                <script>
                    var idList = "{{ $data->id }}";
                    $('#formDelete' + idList).submit(function() {
                        if (!$('#formDelete' + idList).valid()) return false;
                        $('#btnFormDelete' + idList).attr("disabled", "disabled");
                        $('#btnFormDelete' + idList).html('<i class="mdi mdi-loading mdi-spin label-icon"></i>Please Wait...');
                        return true;
                    });
                </script>
            </div>
        </div>
    </div>
    {{-- Modal Posted --}}
    <div class="modal fade" id="posted{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-top" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Posted</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pr.posted', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data" id="formPosted{{ $data->id }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="text-center">
                            Apakah Anda Yakin Untuk <b>Posted</b> Data?
                            <br><b>"{{ $data->request_number }}"</b>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success waves-effect btn-label waves-light" id="btnFormPosted{{ $data->id }}">
                            <i class="mdi mdi-send-check label-icon"></i>Posted
                        </button>
                    </div>
                </form>
                <script>
                    var idList = "{{ $data->id }}";
                    $('#formPosted' + idList).submit(function() {
                        if (!$('#formPosted' + idList).valid()) return false;
                        $('#btnFormPosted' + idList).attr("disabled", "disabled");
                        $('#btnFormPosted' + idList).html('<i class="mdi mdi-loading mdi-spin label-icon"></i>Please Wait...');
                        return true;
                    });
                </script>
            </div>
        </div>
    </div>
@endif


@if(in_array($data->status, ['Posted', 'Created PO', 'Closed']))
    <a href="/print-pr/{{ $data->id }}" class="btn btn-sm btn-info waves-effect waves-light my-half">
        <i class="bx bx-printer" title="Print in English"></i>
    </a>
    <a href="/print-pr-ind/{{ $data->id }}" class="btn btn-sm btn-success waves-effect waves-light my-half">
        <i class="bx bx-printer" title="Cetak dalam Indonesia"></i>
    </a>
@endif

@if(in_array($data->status, ['Posted']))
    @can('PPIC_unposted')
        <button class="btn btn-sm btn-secondary my-half" data-bs-toggle="modal" data-bs-target="#unposted{{ $data->id }}">
            <i class="mdi mdi-arrow-left-top-bold" title="Un Posted" >Un-Posted</i>
        </button>
    @endcan
    {{-- Modal Un-Posted --}}
    <div class="modal fade" id="unposted{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-top" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Un-Posted</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pr.unposted', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data" id="formUnPosted{{ $data->id }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="text-center">
                            Apakah Anda Yakin Untuk <b>Un-Posted</b> Data?
                            <br><b>"{{ $data->request_number }}"</b>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-secondary waves-effect btn-label waves-light" id="btnFormUnPosted{{ $data->id }}">
                            <i class="mdi mdi-arrow-left-top-bold label-icon"></i>Un-Posted
                        </button>
                    </div>
                </form>
                <script>
                    var idList = "{{ $data->id }}";
                    $('#formUnPosted' + idList).submit(function() {
                        if (!$('#formUnPosted' + idList).valid()) return false;
                        $('#btnFormUnPosted' + idList).attr("disabled", "disabled");
                        $('#btnFormUnPosted' + idList).html('<i class="mdi mdi-loading mdi-spin label-icon"></i>Please Wait...');
                        return true;
                    });
                </script>
            </div>
        </div>
    </div>
@endif