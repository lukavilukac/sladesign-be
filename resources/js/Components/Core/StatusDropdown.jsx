import { Fragment } from 'react'
import { Menu, Transition } from '@headlessui/react'
import { ChevronDownIcon } from '@heroicons/react/20/solid'
import {router} from "@inertiajs/react";

function classNames(...classes) {
    return classes.filter(Boolean).join(' ')
}

const StatusDropdown = ({orderId, children}) => {

    const statuses = [
        {
            id: 1,
            name: "Pending"
        },
        {
            id:2,
            name: "In Progress"
        },
        {
            id: 3,
            name: "Sent"
        },
        {
            id: 4,
            name: "Done"
        },
        {
            id: 5,
            name: "Failed"
        }
    ];

    const handleClick = (statusId) => {
        const data = {
            order_status_id: statusId,
            redirectRoute: "admin.tableOrders"
        };
        router.patch(route("admin.orders.update-order-status", orderId), data);
    }

    return (
        <Menu as="div" className="relative inline-block text-left">
            <div>
                <Menu.Button className="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    {children}
                    <ChevronDownIcon className="-mr-1 h-5 w-5 text-gray-400" aria-hidden="true" />
                </Menu.Button>
            </div>

            <Transition
                as={Fragment}
                enter="transition ease-out duration-100"
                enterFrom="transform opacity-0 scale-95"
                enterTo="transform opacity-100 scale-100"
                leave="transition ease-in duration-75"
                leaveFrom="transform opacity-100 scale-100"
                leaveTo="transform opacity-0 scale-95"
            >
                <Menu.Items className="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-hidden">
                    <div className="py-1">
                        {statuses.map((status) =>
                            <Menu.Item>
                                {({ active }) => (
                                    <span
                                        onClick={() => handleClick(status.id)}
                                        className={classNames(
                                            active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                                            'block px-4 py-2 text-sm'
                                        )}
                                    >
                                        {status.name}
                                    </span>
                                )}
                            </Menu.Item>
                        )}
                        <Menu.Item>
                            {({ active }) => (
                                <a
                                    href="#"
                                    className={classNames(
                                        active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                                        'block px-4 py-2 text-sm'
                                    )}
                                >
                                    Account settings
                                </a>
                            )}
                        </Menu.Item>

                    </div>
                </Menu.Items>
            </Transition>
        </Menu>
    )
}

export default StatusDropdown;
