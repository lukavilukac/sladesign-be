import AdminLayout from "@/Layouts/AdminLayout.jsx";
import StatusBadge from "@/Components/Core/StatusBadge.jsx";
import StatusDropdown from "@/Components/Core/StatusDropdown.jsx";
import React, {useState} from "react";

const address = (order) => {
    const shipTo = order.shipping ? order.shipping : order.user;

    return shipTo.country + ', ' + shipTo.city + ', ' + shipTo.address;
}

const Order = ({order}) => {
    const [orderStatus, setOrderStatus] = useState(order.order_status_id);

    return <AdminLayout>

        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="px-4 py-6 sm:px-6">
                <h3 className="text-base font-semibold leading-7 text-gray-900">Order Information</h3>
                <p className="mt-1 max-w-2xl text-sm leading-6 text-gray-500">Order Number: {order.id}</p>
            </div>
            <div className="border-t border-gray-100">
                <dl className="divide-y divide-gray-100">
                    <div className="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt className="text-sm font-medium text-gray-900">Customer Name</dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{order.user.first_name} {order.user.last_name}</dd>
                    </div>
                    <div className="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt className="text-sm font-medium text-gray-900">Customer Email</dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{order.user.email}</dd>
                    </div>
                    <div className="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt className="text-sm font-medium text-gray-900">Shipping Address</dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{address(order)}</dd>
                    </div>
                    <div className="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt className="text-sm font-medium text-gray-900">Price</dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{order.price} rsd</dd>
                    </div>
                    <div className="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt className="text-sm font-medium text-gray-900">Created At</dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{order.created_at.split('T')[0]}</dd>
                    </div>
                    <div className="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt className="text-sm font-medium text-gray-900">Status</dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <StatusDropdown orderId={order.id}>
                                <StatusBadge status={orderStatus} />
                            </StatusDropdown>
                        </dd>
                    </div>
                    <div className="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt className="text-sm font-medium leading-6 text-gray-900">Order Lines</dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            {order.order_lines.map((orderLine, index) => (
                                <div key={orderLine.id} className={'flex items-center pt-2 pb-2 mt-4'}>
                                    <div className="shrink-0">
                                        <img className="h-7 w-7 rounded-full" src={orderLine.product.product_image.path} alt="" />
                                    </div>
                                    <span className="ml-2">{orderLine.product.name}</span>
                                    <div className="ml-2">
                                        {orderLine.modifiers.map(modifier => (
                                            <div key={modifier.id} className="ml-2">{modifier.modifier_group.name}: {modifier.value}: {modifier.description}; {modifier.price} rsd</div>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </dd>
                    </div>
                </dl>
            </div>
            <div class="flex justify-end">
                <a href={route('admin.tableOrders')} class="bg-indigo-600 hover:bg-blue-700 text-white font-bold py-2 px-4 m-2 rounded-sm">
                    Back
                </a>
            </div>
        </div>

    </AdminLayout>
}

export default Order;
