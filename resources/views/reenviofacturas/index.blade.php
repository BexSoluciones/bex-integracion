@extends('layouts.app')

@section('content')

<div class="container-fluid">


    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Reenvío Facturas</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if($facturasError->first())
                    <form class="form-inline my-2 my-lg-0" method="get" action="{{ route('reenviofacturas') }}">
                        @csrf
                        <input class="form-control mr-sm-2" type="search" placeholder="Número de factura" aria-label="Search" name="buscar" id="buscar" value="{{$buscar}}">
                        <button class="btn btn-primary my-4 my-sm-0" type="submit">Buscar</button>
                      </form>
                      <hr>
                        <table class="table table-condensed table-striped" style="font-size:0.7rem;">
                            <thead class="thead-dark">
                              <tr>
                                <th scope="col" width="10%">Número factura</th>
                                <th scope="col" width="10%">Tipo doc.</th>
                                <th scope="col" width="10%">Cent. Opera</th>
                                <th scope="col" width="10%">Fecha factura</th>
                                <th scope="col" width="14%">Fecha webservice</th>
                                <th scope="col" width="6%">Cód. estado</th>
                                <th scope="col" width="34%">Mensaje error</th>
                                <th scope="col" width="6%">Acción</th>
                              </tr>
                            </thead>
                            <tbody>

                                @foreach ($facturasError as $factura)


                                <tr id="fila_{{ $factura->numero_factura }}">
                                    <th scope="row">{{ $factura->numero_factura }}</th>
                                    <td>{{ $factura->tipo_documento }}</td>
                                    <td>{{ $factura->centro_operacion }}</td>
                                    <td>{{ date_format(date_create($factura->fecha_factura),"Y-m-d") }}</td>
                                    <td>{{ $factura->fechamovws }}</td>
                                    <td><h5><span class="badge badge-danger ">{{ $factura->estadoenviows }}</span></h5></td>
                                    <td >{{ $factura->msmovws }}</td>
                                    <td >
                                        <input type="hidden" name="factura" id="factura" value="{{ $factura->numero_factura.'|'.$factura->tipo_documento.'|'.$factura->centro_operacion.'|'.$factura->bodega }}">
                                        <button type="button" class="btn btn-primary reenviar" id="{{ $factura->numero_factura }}">Reenviar</button>
                                    </td>
                                  </tr>
                                @endforeach


                            </tbody>
                          </table>

                          {{ $facturasError->links() }}

                          @else
                          <div class="alert alert-primary" role="alert">
                            No existen facturas pendientes por reenviar.
                          </div>
                          @endif

                </div>
            </div>
        </div>
    </div>
</div>


<script>

// $('#fila_452548').css('background', 'red');

    $(".reenviar").click(function(){

    //    alert($(this).attr("id")) ;
         //declarando objetos
         $textFactura= $(this).siblings('#factura');
         $trFactura= $(this).parent().parent();

         //declarando variables
         factura = $textFactura.val();

        //  $trFactura.css("background", "#e3342f");

        $.ajax({
        async: true,
        cache: false,
        type: 'get',
        url: base_path+'/reenviar-factura',
        data:{
            factura: factura
        },
        beforeSend: function () {
            $(this).attr('value', 'Cargando....');
            console.log("cargando...");
        }
    })
        .done(function (respuesta) {

            console.log(respuesta);

            if(respuesta.renviado==true){

                $trFactura.remove();
                alert(respuesta.mensaje);



            }

        })
        .fail(function (jqXHR, ajaxOptions, thrownError) {
            alert("El servidor no responde");
        });

     });


    </script>
@endsection
