<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //arreglo de pedidos que se filtarn segun su estado (0,1)
        $array = Order::all();

        //arreglo de pedidos vacio
        $orders = array();

        //recorrer cada una de los pedidos que se tienen
        foreach ($array as $order) {
            //arreglo de productos de cada pedido
            $orderCart = array();

            //recorrer el detalle de cada venta -> los productos que se vendieron
            foreach ($order->orderdetails as $orderdetail) {
                //llenar el arreglo de productos que se tiene en cada pedido
                array_push(
                    $orderCart,
                    [
                        "id" => $orderdetail->id,
                        "name" => $orderdetail->product->name,
                        "amount" => $orderdetail->amount,
                        "price_order" => $orderdetail->price_order,
                        "subtotal" => $orderdetail->amount * $orderdetail->precio_order,
                    ]
                );
            }

            //manipular fecha y hora para un mejor formato
            $date = $order->created_at->format('Y-m-d');
            $hora = $order->created_at->format('g:i A');
            //llenar el arreglo de las pedidos que se tienen
            array_push(
                $orders,
                [
                    "id" => $order->id,
                    "status" => $order->status,
                    "user_id" => $order->user_id,
                    "user_name" => $order->user->name,
                    "date" =>  $date,
                    "hour" => $hora,
                    "total" => $order->total,
                    "orderCart" => $orderCart
                ]
            );
        }
        //retornar los pedidos que se tienen
        return  $orders;
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
            'orderCart' => 'required|array'
        ]);

        //retornar con error si la validacion fallo
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        //variable para sumar el total del pedido
        $total = 0;

        foreach ($request->orderCart as $product) {
            //enocontrar el producto que se vende
            $pro = Product::find($product['id'])->only('id', 'price_order', 'stock');

            //suma el total de la venta
            $total += $product['amount'] * $pro['price_order'];
        }
        $user = auth()->user();
        //guardar la venta en la bd
        $order = Order::create([
            'total' => $total,
            'user_id' =>  1, //$user->id, //CAMBIAR CUANDO YA HAYA AUTENTICACION
            'status' => 1
        ]);


        //array de los productos que se piden
        $orderCart = array();

        foreach ($request->orderCart as $product) {
            //encontrar el producto que se pide
            $pro = Product::find($product['id']);

            //guardar el detalle del pedido
            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product['id'],
                'amount' => $product['amount'],
                'price_order' => $pro->price_order
            ]);

            //aumentar stock de los productos que se piden
            $pro->stock = $pro->stock + $product['amount'];
            $pro->save();

            //crear un arreglo con los productos que se vendieron
            array_push(
                $orderCart,
                [
                    "id" => $product['id'],
                    "name" => $pro->name,
                    "amount" => $product['amount'],
                    "price_order" => $pro->price_order
                ]
            );
        };

        //manipular hora para mejor formato
        $order->hour = $order->created_at->format('g:i A');

        //manipular fecha para mejor formato
        $order->date = $order->created_at->format('Y-m-d');

        //mandar una respuesta satisfactoria
        return response()->json([
            "success" => true,
            "message" => "Pedido Creado con éxito",
            "order" => $order
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //encontrar el id del pedido
        $id = $order->id;
        //$order = Order::find('id', $id);

        //verificar si existe el pedido
        if (is_null($order)) {
            return response()->json([
                "success" => false,
                "message" => 'El pedido No.' . $id . ' no existe',
                "data" => ["order" => $id]
            ]);
        }

        //arreglo de productos que se pidieron
        $products = array();
        $total = 0;
        //recorrer los detalles del pedido
        foreach ($order->orderdetails as $orderdetail) {
            $total += $orderdetail['amount'] * $orderdetail['price_order'];
            array_push($products, [
                "id" => $orderdetail['product_id'],
                "name" => $orderdetail->product['name'],
                "amount" => $orderdetail['amount'],
                "price_order" => $orderdetail['price_order']
            ]);
        }

        //mandar una respuesta satisfactoria con los datos de la venta
        return response()->json([
            "success" => true,
            "message" => "Sale retrieved successfully.",
            "data" => [
                "sale" => [
                    "id" => $order->id,
                    "user_id" => $order->user_id,
                    "user_name" => $order->user['name'],
                    "total" => $total,
                    "date" => $order->created_at
                ],
                "products" => $products
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //verificar si el pedido no esta cancelado -> status !== 0
        if ($order->status === 0) {
            //mandar mensaje de error si el pedido ya esta cancelada
            return response()->json([
                "success" => false,
                "message" => 'El pedido No.' . $order->id . ' ya esta cancelado',
                "order_id" => $order->id
            ]);
        }

        //actualizar el status del pedido
        $order->status = 0;
        $order->save();

        //recorrer los detalles del pedido
        foreach ($order->orderdetails as $orderdetail) {
            //disminuir stock de los productos
            $orderdetail->product->stock -= $orderdetail->amount;
            $orderdetail->product->save();
        }

        return response()->json([
            "success" => true,
            "message" => "Order cancelad successfully.",
            "order_id" => $order->id
        ]);
    }

    public function ordersuser(Request $request, User $user)
    {
        //arreglo de pedidos 
        $array = $user->orders;
        //arreglo de pedidos
        $orders = array();

        //recorrer cada uno de los pedidos que se tienen
        foreach ($array as $order) {
            //arreglo de productos de cada prdido
            $products = array();

            if ($order->status == $request->status) {
                //recorrer el detalle de cada pedido -> los productos que se pidieron
                foreach ($order->orderdetails as $orderdetail) {
                    //llenar el arreglo de productos que se tiene en cada pedido
                    array_push(
                        $products,
                        [
                            "id" => $orderdetail->product->id,
                            "name" => $orderdetail->product->name,
                            "amount" => $orderdetail->amount,
                            "price_order" => $orderdetail->price_order,
                            "subtotal" => $orderdetail->amount * $orderdetail->price_order,
                        ]
                    );
                }
                //llenar el arreglo de los pedidos que se tienen
                array_push(
                    $orders,
                    [
                        "id" => $order->id,
                        "user_id" => $order->user_id,
                        "user_name" => $order->user->name,
                        "total" => $order->total,
                        "products" => $products
                    ]
                );
            }
        }
        //retornar los pedidos que se tienen
        return response()->json([
            "success" => true,
            "message" => "Orders",
            "orders" => $orders

        ]);
    }

    public function ordersdate(Request $request)
    {
        //arreglo de ventas segun la fecha
        $array = Order::orWhere('created_at', 'like', $request->date . "%")->get();

        //arreglo de pedidos
        $orders = array();

        //recorrer cada uno de los pedidos que se tienen
        foreach ($array as $order) {
            //arreglo de productos de cada prdido
            $products = array();
            //return $request->status;
            if ($order->status == $request->status) {
                //recorrer el detalle de cada venta -> los productos que se vendieron
                foreach ($order->orderdetails as $orderdetail) {
                    //llenar el arreglo de productos que se tiene en cada venta
                    array_push(
                        $products,
                        [
                            "id" => $orderdetail->product->id,
                            "name" => $orderdetail->product->name,
                            "amount" => $orderdetail->amount,
                            "price_order" => $orderdetail->price_order,
                            "subtotal" => $orderdetail->amount * $orderdetail->price_order,
                        ]
                    );
                }
                //llenar el arreglo de los pedidos que se tienen
                array_push(
                    $orders,
                    [
                        "id" => $order->id,
                        "user_id" => $order->user_id,
                        "user_name" => $order->user->name,
                        "total" => $order->total,
                        "products" => $products
                    ]
                );
            }
        }
        //retornar los pedidos que se tienen
        return response()->json([
            "success" => true,
            "message" => "Orders",
            "orders" => $orders

        ]);
    }
}
