<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use App\Models\Client;
use App\Models\Product;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //arreglo de ventas 
        $array = Sale::all();

        //arreglo de ventas
        $sales = array();

        //rtecorrer cada una de las ventas que se tienen
        foreach ($array as $sale) {
            //arreglo de productos de cada venta
            $shoppingCart = array();

            //recorrer el detalle de cada venta -> los productos que se vendieron
            foreach ($sale->saledetails as $saledetail) {
                //llenar el arreglo de productos que se tiene en cada venta
                array_push(
                    $shoppingCart,
                    [
                        "id" => $saledetail->product->id,
                        "name" => $saledetail->product->name,
                        "amount" => $saledetail->amount,
                        "price_sale" => $saledetail->price_sale,
                        "subtotal" => $saledetail->amount * $saledetail->price_sale,
                    ]
                );
            }

            //manipular fecha y hora para un mejor formato
            $date = $sale->created_at->format('Y-m-d');
            $hora = $sale->created_at->format('g:i A');

            //llenar el arreglo de las ventas que se tienen
            array_push(
                $sales,
                [
                    "id" => $sale->id,
                    "status" => $sale->status,
                    "user_id" => $sale->user_id,
                    "user_name" => $sale->user->name,
                    "date" =>  $date,
                    "hour" => $hora,
                    "total" => $sale->total,
                    "shoppingCart" => $shoppingCart
                ]
            );
        }



        //retornar las ventas que se tienen
        return $sales;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'shoppingCart' => 'required|array'
        ]);

        //retornar con error si la validacion fallo
        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                $validator->errors()
            ]);
        }

        //variable para sumar el total de la venta
        $total = 0;

        foreach ($request->shoppingCart as $product) {
            //enocontrar el producto que se vende
            $pro = Product::find($product['id'])->only('id', 'name', 'price_sale', 'stock');
            //comprobar que hay stoy suficiente de cada producto
            if ($pro['stock'] == 0) {
                //retornar una respuesta con error que no hay stock de un producto
                return response()->json([
                    "success" => false,
                    "message" => 'No hay stock del producto: ' . $pro['name'],
                    "data" => ["product_id" => $pro['id'], "stock" => $pro['stock']]
                ]);
            } else if ($product['amount'] > $pro['stock']) {
                //retornar una respuesta con error si no hay stock suficiente de un producto
                return response()->json([
                    "success" => false,
                    "message" => 'Solo hay ' . $pro['stock'] . ' unidades del producto: ' . $pro['name'],
                    "data" => ["product_id" => $pro['id'], "stock" => $pro['stock']]
                ]);
            }
            //suma el total de la venta
            $total += $product['amount'] * $pro['price_sale'];
        }
        //poner al usuario autenticado
        $user = auth()->user();

        //guardar la venta en la bd
        $sale = Sale::create([
            'total' => $total,
            'user_id' => 1, //$user->id, //CAMBIAR CUANDO YA HAYA AUTENTICACION
            'status' => 1
        ]);


        //array de los productos que se venden
        $shoppingCart = array();

        foreach ($request->shoppingCart as $product) {
            //encontrar el producto que se vende
            $pro = Product::find($product['id']);

            //guardar el detalle de la venta
            SaleDetail::create([
                'sale_id' => $sale->id,
                'product_id' => $product['id'],
                'amount' => $product['amount'],
                'price_sale' => $pro->price_sale
            ]);

            //disminuir stock de los productos que se venden
            $pro->stock = $pro->stock - $product['amount'];
            $pro->save();

            //crear un arreglo con los productos que se vendieron
            array_push(
                $shoppingCart,
                [
                    "id" => $product['id'],
                    "name" => $pro->name,
                    "amount" => $product['amount'],
                    "price_sale" => $pro->price_sale
                ]
            );
        }
        //manipular hora para mejor formato
        $sale->hour =  $sale->created_at->format('g:i A');

        //manipular fecha para mejor formato
        $sale->date = $sale->created_at->format('Y-m-d');

        //mandar una respuesta satisfactoria
        return response()->json([
            "success" => true,
            "message" => "Venta creada con éxito",
            "sale" => $sale,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function show(Sale $sale)
    {
        //encontrar el id de la venta
        $id = $sale->id;

        //verificar si existe la venta
        if (is_null($sale)) {
            return response()->json([
                "success" => false,
                "message" => 'La venta ' . $id . ' no existe',
                "data" => ["sale" => $id]
            ]);
        }
        //arreglo de productos que se vendieron
        $shoppingCart = array();
        $total = 0;
        //recorrer los detalles de la venta
        foreach ($sale->saledetails as $saledetail) {
            $total += $saledetail['amount'] * $saledetail['price_sale'];
            array_push($shoppingCart, [
                "id" => $saledetail['product_id'],
                "name" => $saledetail->product['name'],
                "amount" => $saledetail['amount'],
                "price_sale" => $saledetail['price_sale']
            ]);
        }

        //mandar una respuesta satisfactoria con los datos de la venta
        return response()->json([
            "success" => true,
            "message" => "Sale retrieved successfully.",
            "data" => [
                "sale" => [
                    "id" => $sale->id,
                    "user_id" => $sale->user_id,
                    "user_name" => $sale->user['name'],
                    "total" => $total,
                    "date" => $sale->created_at
                ],
                "shoppingCart" => $shoppingCart
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sale $sale)
    {
        //verificar si la venta no esta cancelada -> status !== 0
        if ($sale->status === 0) {
            //mandar mensaje de error si la venta ya esta cancelada
            return response()->json([
                "success" => false,
                "message" => 'La venta No.' . $sale->id . ' ya esta cancelada',
                "data" => ["sale" => $sale->id]
            ]);
        }

        //actualizar el status de la venta
        $sale->status = 0;
        $sale->save();

        //recorrer los detalles de la venta
        foreach ($sale->saledetails as $saledetail) {
            //aumentar stock de los productos
            $saledetail->product->stock += $saledetail->amount;
            $saledetail->product->save();
        }

        return response()->json([
            "success" => true,
            "message" => "Sale cancelad successfully.",
            "sale_id" => $sale->id
        ]);
    }

    /*
    public function salesclient(Request $request, Client $client)
    {
        $noSales = 0;
        //arreglo de ventas 
        $array = $client->sales;

        //arreglo de ventas
        $sales = array();

        //recorrer cada una de las ventas que se tienen
        foreach ($array as $sale) {

            //arreglo de productos de cada venta
            $shoppingCart = array();
            //return $request->status;
            if ($sale->status == $request->status) {
                $noSales++;
                //recorrer el detalle de cada venta -> los productos que se vendieron
                foreach ($sale->saledetails as $saledetail) {
                    //llenar el arreglo de productos que se tiene en cada venta
                    array_push(
                        $shoppingCart,
                        [
                            "id" => $saledetail->product->id,
                            "name" => $saledetail->product->name,
                            "amount" => $saledetail->amount,
                            "price_sale" => $saledetail->price_sale,
                            "subtotal" => $saledetail->amount * $saledetail->price_sale,
                        ]
                    );
                }
                //llenar el arreglo de las ventas que se tienen
                array_push(
                    $sales,
                    [
                        "id" => $sale->id,
                        "user_id" => $sale->user_id,
                        "user_name" => $sale->user->name,
                        "client_id" => $sale->client_id,
                        "client_name" => $sale->client->name,
                        "total" => $sale->total,
                        "shoppingCart" => $shoppingCart
                    ]
                );
            }
        }
        //retornar las ventas que se tienen
        return response()->json([
            "success" => true,
            "message" => "Sales",
            "NoSales" => $noSales,
            "sales" => $sales

        ]);
    }

    */


    public function salesuser(Request $request, User $user)
    {
        $noSales = 0;
        //arreglo de ventas 
        $array = $user->sales;
        //arreglo de ventas
        $sales = array();

        //recorrer cada una de las ventas que se tienen
        foreach ($array as $sale) {

            //arreglo de productos de cada venta
            $shoppingCart = array();
            //return $request->status;
            if ($sale->status == $request->status) {
                $noSales++;
                //recorrer el detalle de cada venta -> los productos que se vendieron
                foreach ($sale->saledetails as $saledetail) {
                    //llenar el arreglo de productos que se tiene en cada venta
                    array_push(
                        $shoppingCart,
                        [
                            "id" => $saledetail->product->id,
                            "name" => $saledetail->product->name,
                            "amount" => $saledetail->amount,
                            "price_sale" => $saledetail->price_sale,
                            "subtotal" => $saledetail->amount * $saledetail->price_sale,
                        ]
                    );
                }
                //llenar el arreglo de las ventas que se tienen
                array_push(
                    $sales,
                    [
                        "id" => $sale->id,
                        "user_id" => $sale->user_id,
                        "user_name" => $sale->user->name,
                        "created_at" => $sale->created_at,
                        "total" => $sale->total,
                        "shoppingCart" => $shoppingCart
                    ]
                );
            }
        }
        //retornar las ventas que se tienen
        return response()->json([
            "success" => true,
            "message" => "Sales",
            "No. sales" => $noSales,
            "sales" => $sales

        ]);
    }

    public function salesdate(Request $request)
    {

        //validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'status' => 'required|numeric',
            'date' => 'required|date'
        ]);
        /** 
        //retornar con error si la validacion fallo
        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                $validator->errors()
            ]);
        }
         */
        $noSales = 0;
        //arreglo de ventas segun la fecha
        $array = Sale::orWhere('created_at', 'like', $request->date . "%")->get();

        //arreglo de ventas
        $sales = array();

        //recorrer cada una de las ventas que se tienen
        foreach ($array as $sale) {

            //arreglo de productos de cada venta
            $shoppingCart = array();
            //return $request->status;
            if ($sale->status == $request->status) {
                $noSales++;
                //recorrer el detalle de cada venta -> los productos que se vendieron
                foreach ($sale->saledetails as $saledetail) {
                    //llenar el arreglo de productos que se tiene en cada venta
                    array_push(
                        $shoppingCart,
                        [
                            "id" => $saledetail->product->id,
                            "name" => $saledetail->product->name,
                            "amount" => $saledetail->amount,
                            "price_sale" => $saledetail->price_sale,
                            "subtotal" => $saledetail->amount * $saledetail->price_sale,
                        ]
                    );
                }
                //llenar el arreglo de las ventas que se tienen
                array_push(
                    $sales,
                    [
                        "id" => $sale->id,
                        "user_id" => $sale->user_id,
                        "user_name" => $sale->user->name,
                        "created_at" => $sale->created_at,
                        "total" => $sale->total,
                        "shoppingCart" => $shoppingCart
                    ]
                );
            }
        }
        //retornar las ventas que se tienen
        return response()->json([
            "success" => true,
            "message" => "Sales",
            "noSales" => $noSales,
            "sales" => $sales

        ]);
    }
}
