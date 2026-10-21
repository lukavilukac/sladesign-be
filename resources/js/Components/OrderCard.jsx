import { CalendarDaysIcon, CreditCardIcon, UserCircleIcon, CurrencyDollarIcon, MapPinIcon } from '@heroicons/react/20/solid'
import MainModal from "@/Components/Modals/MainModal.jsx";
import {useState} from "react";
import OrderModalContent from "@/Components/Modals/Contents/OrderModalContent.jsx";

export default function OrderCard({order, toggleOpenModal}) {

    const [openModal, setOpenModal] = useState(false);

    const toggleModal = () => {
        setOpenModal(true);
    }

    const address = () => {
        const shipTo = order.shipping ? order.shipping : order.user;

        return shipTo.country + ', ' + shipTo.city + ', ' + shipTo.address;
    }

    return (
            <>
                <MainModal open={openModal} setOpen={setOpenModal}>
                    <OrderModalContent order={order} address={address()} setOpenModal={setOpenModal}/>
                </MainModal>
                <div className="overflow-hidden rounded-xl border border-gray-200 shadow-sm bg-white">

                    <div className="border-b border-gray-200 px-1 py-1">
                        <div className="-ml-1 -mt-1 flex flex-wrap items-center justify-between sm:flex-nowrap">
                            <div className="ml-1 mt-1">
                                <h3 className="text-base text-xs font-semibold leading-6 text-gray-900">{order.user.first_name + ' ' + order.user.last_name}</h3>
                            </div>
                            <div className="ml-1 mt-1 shrink-0">
                                <button
                                    onClick={toggleModal}
                                    type="button"
                                    className="relative inline-flex items-center rounded-md bg-indigo-600 px-1 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                >
                                    Details
                                </button>
                            </div>
                        </div>
                    </div>

                    <dl className="-my-2 divide-y divide-gray-100 px-1 py-1 text-sm leading-6">

                        <div className="flex justify-between gap-x-1 pt-4 pb-2">
                            <dt className="text-xs text-gray-500"><CurrencyDollarIcon className={'h5 w-5'}/></dt>
                            <dd className="flex items-start gap-x-2">
                                <div className="text-xs font-bold text-gray-900">{order.price}</div>
                            </dd>
                        </div>

                        <div className="flex justify-between gap-x-1 py-2">
                            <dt className="text-xs text-gray-500"><CalendarDaysIcon className={'h-4 w-4'} /></dt>
                            <dd className="flex items-start gap-x-2">
                                <div className="text-xs font-medium text-gray-900">{order.created_at.split('T')[0]}</div>
                            </dd>
                        </div>

                        <div className="flex justify-between gap-x-1 py-2">
                            <dt className="text-xs text-gray-500"><MapPinIcon className={'h5 w-5'}/></dt>
                            <dd className="flex items-start gap-x-2">
                                <div className="text-xs font-medium text-gray-900">{address()}</div>
                            </dd>
                        </div>
                    </dl>

                </div>
            </>
    )
}
