import AdminLayout from "@/Layouts/AdminLayout.jsx";
import axios from "axios";
import React, {useEffect, useState} from 'react';
import StatusBadge from "@/Components/Core/StatusBadge.jsx";
import StatusDropdown from "@/Components/Core/StatusDropdown.jsx";
import {usePage} from "@inertiajs/react";

const TableOrders = () => {

    const [orders, setOrders] = useState([]);

    const { props } = usePage();
    const { flash } = props;

    const address = (order) => {
        const shipTo = order.shipping ? order.shipping : order.user;

        return shipTo.country + ', ' + shipTo.city + ', ' + shipTo.address;
    }

    useEffect(() => {
        // Function to fetch data from the API
        const fetchOrders = async () => {
            try {
                const response = await axios.get(route('admin.orders.all'));
                // const json = await response.json();
                setOrders(response.data.json.orders);
                setFetchOrdersFlag(false);
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        };

        // Call the fetchData function on page load
        fetchOrders();
    }, [flash]);

    return <AdminLayout>

        <div className="px-4 sm:px-6 lg:px-8">
            <div className="sm:flex sm:items-center">
                <div className="sm:flex-auto">
                    <h1 className="text-base font-semibold leading-6 text-gray-900">Orders</h1>
                    <p className="mt-2 text-sm text-gray-700">
                        A list of all the orders including their info.
                    </p>
                </div>
{/*                <div className="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <a
                        href={route('admin.modifiers.new')}
                        type="button"
                        className="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    >
                        Add Modifier
                    </a>
                </div>*/}
            </div>
            <div className="mt-8 flow-root">
                <div className="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div className="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table className="min-w-full divide-y divide-gray-300">
                            <thead>
                            <tr>
                                <th scope="col" className="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                    Address
                                </th>
                                <th scope="col" className="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                    Customer
                                </th>
                                <th scope="col" className="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                    Date
                                </th>
                                <th scope="col" className="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                    Price
                                </th>
                                <th scope="col" className="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                    Status
                                </th>
                                <th scope="col" className="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                    <span className="sr-only">Edit</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                            {orders && Object.entries(orders).map(([orderKey, order])  => (
                                <tr key={order.id}>
                                    <td className="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                        <div className="flex items-center">
                                            <div className="h-11 w-11 shrink-0">
                                                {/*<img className="h-11 w-11 rounded-full" src={modifier.image} alt="" />*/}
                                            </div>
                                            <div className="ml-4">
                                                <div className="font-medium text-gray-900">{address(order)}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div className="flex items-center">
                                            <div className="ml-4">
                                                <div className="font-medium text-gray-900">{order.user.first_name + ' ' + order.user.last_name}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div className="flex items-center">
                                            <div className="ml-4">
                                                <div className="font-medium text-gray-900">{order.order_date}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div className="flex items-center">
                                            <div className="ml-4">
                                                <div className="font-medium text-gray-900">{order.price}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div className="flex items-center">
                                            <div className="ml-4">
                                                <StatusDropdown orderId={order.id}>
                                                    <StatusBadge status={order.order_status_id} />
                                                </StatusDropdown>

                                            </div>
                                        </div>
                                    </td>
                                    <td className="relative whitespace-nowrap py-5 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                        <a href={route('admin.orders.show', order.id)} className="text-indigo-600 hover:text-indigo-900">
                                            View<span className="sr-only">, {order.value}</span>
                                        </a>
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
}

export default TableOrders;
