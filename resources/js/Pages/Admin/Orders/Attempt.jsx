import AdminLayout from '@/Layouts/AdminLayout.jsx';
import OrderHistory, {JsonDetails} from '@/Components/OrderHistory.jsx';

export default function Attempt({attempt}) {
    return <AdminLayout>
        <div className="rounded-lg bg-white p-6 shadow-sm">
            <a href="/admin/order-attempts" className="text-sm text-indigo-700">← All attempts</a>
            <h1 className="mt-3 break-all text-xl font-semibold">Attempt {attempt.id}</h1>
            <p className="mt-2">{attempt.status.replaceAll('_', ' ')} · {new Date(attempt.created_at).toLocaleString()}</p>
            {attempt.order_id && <a className="mt-2 block text-indigo-700" href={`/admin/orders/${attempt.order_id}`}>Open order #{attempt.order_id}</a>}
            {attempt.checkout_id && <a className="mt-2 block text-indigo-700" href={`/admin/order-attempts?checkout_id=${attempt.checkout_id}`}>Related attempts for checkout {attempt.checkout_id}</a>}
            <JsonDetails title="Received payload (business fields, secrets excluded)" value={attempt.payload}/>
            <JsonDetails title="Validated payload" value={attempt.validated_payload}/>
        </div>
        <OrderHistory endpoint={`/admin/order-attempts/${attempt.id}/history`}/>
    </AdminLayout>;
}
