import AdminLayout from '@/Layouts/AdminLayout.jsx';

const statuses = ['received', 'processing', 'validation_failed', 'failed', 'created', 'created_with_mail_errors'];
export default function Attempts({attempts, filters}) {
    return <AdminLayout>
        <div className="rounded-lg bg-white p-6 shadow-sm">
            <h1 className="text-xl font-semibold">Order Attempts</h1>
            <p className="mt-2 text-sm text-gray-600">Includes failed requests that never created an order. Checkout ID groups retries only when supplied by the client.</p>
            <form method="get" className="my-5 flex flex-wrap gap-3">
                <select name="status" aria-label="Attempt status" defaultValue={filters.status ?? ''} className="rounded border-gray-300"><option value="">All statuses</option>{statuses.map(status => <option key={status} value={status}>{status.replaceAll('_', ' ')}</option>)}</select>
                <input name="checkout_id" aria-label="Checkout ID" placeholder="Checkout UUID" defaultValue={filters.checkout_id ?? ''} className="rounded border-gray-300"/>
                <button className="rounded bg-indigo-600 px-4 py-2 text-white">Filter</button>
                <a href="/admin/order-attempts" className="px-2 py-2 text-indigo-700">Reset</a>
            </form>
            <div className="overflow-x-auto"><table className="w-full text-left text-sm"><thead><tr><th className="py-3">Time</th><th>Attempt</th><th>Status</th><th>Order</th></tr></thead><tbody>
                {attempts.data.map(attempt => <tr key={attempt.id} className="border-t border-gray-200">
                    <td className="py-3 pr-3">{new Date(attempt.created_at).toLocaleString()}</td>
                    <td className="pr-3"><a className="text-indigo-700" href={`/admin/order-attempts/${attempt.id}`}>{attempt.id}</a></td>
                    <td className="pr-3">{attempt.status.replaceAll('_', ' ')}</td>
                    <td>{attempt.order_id ? <a className="text-indigo-700" href={`/admin/orders/${attempt.order_id}`}>#{attempt.order_id}</a> : 'Not created'}</td>
                </tr>)}
            </tbody></table></div>
            {!attempts.data.length && <p className="py-4">No attempts found.</p>}
            <div className="mt-5 flex justify-between text-sm">
                {attempts.prev_page_url ? <a href={attempts.prev_page_url}>Previous</a> : <span/>}
                <span>Page {attempts.current_page} / {attempts.last_page}</span>
                {attempts.next_page_url ? <a href={attempts.next_page_url}>Next</a> : <span/>}
            </div>
        </div>
    </AdminLayout>;
}
