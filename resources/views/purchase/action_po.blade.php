@if($data->status=='Request' or $data->status=='Un Posted')
        <form action="/hapus_po/{{ $data->id }}" method="post"
            class="d-inline">
            @method('delete')
            @csrf
            <!-- <button type="button" class="btn btn-sm btn-danger"
                onclick="hapusData($(this).closest('form'))"> -->
            <button type="submit" class="btn btn-sm btn-danger"
            onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                <i class="bx bx-trash-alt" title="Hapus data" ></i>
            </button>
        </form>
        <a href="/print-po/{{ $data->id }}" class="btn btn-sm btn-info waves-effect waves-light">
                <i class="bx bx-printer" title="print in English"></i>
        </a>
        <a href="/print-po-ind/{{ $data->id }}" class="btn btn-sm btn-success waves-effect waves-light">
                <i class="bx bx-printer" title="print dalam B Indo"></i>
        </a>

        <a href="/edit-po/{{ $data->id }}" class="btn btn-sm btn-info waves-effect waves-light">
                <i class="bx bx-edit-alt" title="Edit data"></i>
        </a>
        @if($data->status=='Request' or $data->status=='Un Posted')
        <form action="/posted_po/{{ $data->id }}" method="post"
            class="d-inline" data-id="">
            @method('PUT')
            @csrf
            <button type="submit" class="btn btn-sm btn-success"
            onclick="return confirm('Anda yakin mau Posted item ini ?')">
                <i class="bx bx-paper-plane" title="Posted" ></i>
                <!-- <i class="mdi mdi-arrow-left-top-bold" title="Posted" >Un Posted</i> -->
            </button></center>
        </form>
        @elseif($data->status=='Posted')
        <form action="/unposted_po/{{ $data->id }}" method="post"
            class="d-inline" data-id="">
            @method('PUT')
            @csrf
            @can('PPIC_unposted')
            <button type="submit" class="btn btn-sm btn-primary"
            onclick="return confirm('Anda yakin mau Un Posted item ini ?')">
                <!-- <i class="bx bx-paper-plane" title="Posted" ></i> -->
                <i class="mdi mdi-arrow-left-top-bold" title="Un Posted" >Un Posted</i>
            </button></center>
            @endcan
        </form>
        @endif
 @elseif($data->status=='Posted')
        <a href="/print-po/{{ $data->id }}" class="btn btn-sm btn-info waves-effect waves-light">
                <i class="bx bx-printer" title="print in English"></i>
        </a>
        <a href="/print-po-ind/{{ $data->id }}" class="btn btn-sm btn-success waves-effect waves-light">
                <i class="bx bx-printer" title="print dalam B Indo"></i>
        </a>
        <form action="/unposted_po/{{ $data->id }}" method="post"
            class="d-inline" data-id="">
            @method('PUT')
            @csrf
            @can('PPIC_unposted')
            <button type="submit" class="btn btn-sm btn-primary"
            onclick="return confirm('Anda yakin mau Un Posted item ini ?')">
                <!-- <i class="bx bx-paper-plane" title="Posted" ></i> -->
                <i class="mdi mdi-arrow-left-top-bold" title="Un Posted" >Un Posted</i>
            </button></center>
            @endcan
        </form>
@elseif($data->status=='Closed')
        <a href="/print-po/{{ $data->id }}" class="btn btn-sm btn-info waves-effect waves-light">
                <i class="bx bx-printer" title="print in English"></i>
        </a>
        <a href="/print-po-ind/{{ $data->id }}" class="btn btn-sm btn-success waves-effect waves-light">
                <i class="bx bx-printer" title="print dalam B Indo"></i>
        </a>
 @endif