import AdminLayout from "@/Layouts/AdminLayout.jsx";
import {useEffect, useState} from "react";
import axios from "axios";
import {DragDropContext} from "@hello-pangea/dnd";
import Column from "@/Components/Column";
import {router} from "@inertiajs/react";

const Orders = () => {

    const [orders, setOrders] = useState(null);

    const onDragEnd = (e) => { //after card is moved to other column, this function saves state

        const {destination, source, draggableId} = e;
        if (!destination) {
            return;
        }

        if (destination.droppableId === source.droppableId) {// only columns are important, if it is the same column - ignore
            return;
        }

        const sourceColumn = orders.columns[source.droppableId];
        const destinationColumn = orders.columns[destination.droppableId];
        const sourceOrderIds = Array.from(sourceColumn.orderIds);
        sourceOrderIds.splice(source.index, 1);
        sourceColumn.orderIds = sourceOrderIds;
        const destinationOrderIds = Array.from(destinationColumn.orderIds);
        destinationOrderIds.splice(destination.index, 0, draggableId);
        destinationColumn.orderIds = destinationOrderIds;

        const newOrder = {
            ...orders,
            columns: {
                ...orders.columns,
                [sourceColumn.id]: sourceColumn,
                [destinationColumn.id]: destinationColumn
            }
        }

        setOrders(newOrder);

        //send update order status request, this should be optimized
        const data = {
            order_status_id: destinationColumn.id
        }

        router.patch(route('admin.orders.update-order-status', draggableId), data);
    }

    const fetchOrders = async () => {
        const {data} = await axios.get(route('admin.orders.all')); //map ids to become string values

        const columnOrder = data.json.columnOrder.map(el=>el.toString());

        const orders = {};

        Object.entries(data.json.orders).map(([key,val]) => {
            const order = {...val};
            order.id = val.id.toString();
            orders[key] = order;
        });

        const columns ={};

        Object.entries(data.json.columns).map(([key,val])=>{
            const col = {...val};
            col.id = val.id.toString();
            columns[key] = col;
        });

        setOrders({columnOrder, orders, columns});
    }

    useEffect(() => {
        fetchOrders();
    },[]);

    return <AdminLayout>

        {orders && <DragDropContext onDragEnd={onDragEnd}>
            <div className={'flex gap-2'}>
                {
                    orders.columnOrder.map((column) => {
                        const col = orders.columns[column];
                        const _orders = col.orderIds.map((id) => {
                            return orders.orders[id];
                        })

                        return <Column title={col.title} items={_orders} id={col.id} key={col.id}/>
                    })
                }
            </div>

        </DragDropContext>}

    </AdminLayout>
}

export default Orders;
