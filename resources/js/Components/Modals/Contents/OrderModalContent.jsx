import {
    AtSymbolIcon,
    CalendarIcon,
    CurrencyDollarIcon,
    MapPinIcon,
    UserIcon
} from '@heroicons/react/20/solid'
import {XMarkIcon} from "@heroicons/react/24/outline";

const OrderModalContent = ({setOpenModal, order, address}) => {

    const closeModal = () => {
        setOpenModal(false);
    }

    return (
        <div className={'relative'}>
            <XMarkIcon onClick={closeModal} className={'h-5 w-5 absolute top-1 right-1 cursor-pointer'} />

            <div className="px-4 py-6 sm:px-6">
                <h3 className="text-base font-semibold leading-7 text-gray-900">Order Number</h3>
                <p className="mt-1 max-w-2xl text-sm leading-6 text-gray-500">{order.id}</p>
            </div>

            <div className="mt-6 border-t border-gray-100">
                <dl className="divide-y divide-gray-100">
                    <div className="bg-white px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-3">
                        <dt className="text-sm font-medium leading-6 text-gray-900"><UserIcon className={'h-6 w-6'} /> Customer Name</dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{order.user.first_name} {order.user.last_name}</dd>
                    </div>
                    <div className="bg-gray-50 px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-3">
                        <dt className="text-sm font-medium leading-6 text-gray-900"><AtSymbolIcon className={'h-6 w-6'} /> Customer Email</dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{order.user.email}</dd>
                    </div>
                    <div className="bg-white px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-3">
                        <dt className="text-sm font-medium leading-6 text-gray-900"><MapPinIcon className={'h-6 w-6'}/> Shipping address</dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{address}</dd>
                    </div>
                    <div className="bg-gray-50 px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-3">
                        <dt className="text-sm font-medium leading-6 text-gray-900"><CurrencyDollarIcon className={'h-6 w-6'}/> Price</dt>
                        <dd className="mt-1 text-sm font-semibold leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{order.price}</dd>
                    </div>
                    <div className="bg-white px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-3">
                        <dt className="text-sm font-medium leading-6 text-gray-900"><CalendarIcon className={'h-6 w-6'}/> Created At</dt>
                        <dd className="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{order.created_at.split('T')[0]}</dd>
                    </div>
                    <div className="bg-gray-50 px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-3">
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
        </div>
    )
}

export default OrderModalContent;
