import {useEffect, useState} from 'react';
import axios from 'axios';

const labels = {
    request_received: 'Request received', validation_passed: 'Validation passed',
    validation_failed: 'Validation failed', customer_prepared: 'Customer prepared',
    shipping_prepared: 'Shipping prepared', order_committed: 'Order saved',
    creation_failed: 'Order creation failed', request_failed: 'Request failed',
    mail_sending: 'Sending email', mail_sent: 'Email accepted by mailer', mail_failed: 'Email failed',
    change_requested: 'Change requested', change_failed: 'Change failed',
    'order.created': 'Order created', 'order.updated': 'Order updated', 'order.deleted': 'Order deleted',
    'orderline.created': 'Item added', 'orderline.updated': 'Item changed', 'orderline.deleted': 'Item removed',
    'orderlinemodifier.created': 'Modifier added', 'orderlinemodifier.deleted': 'Modifier removed',
    'shipping.updated': 'Shipping changed', 'user.updated': 'Customer details changed',
};
const statusNames = {1: 'Pending', 2: 'In Progress', 3: 'Sent', 4: 'Done', 5: 'Failed'};
const valueText = (key, value) => key === 'order_status_id' ? (statusNames[value] ?? value) : JSON.stringify(value ?? null);

export function JsonDetails({title, value}) {
    return <details className="mt-3">
        <summary className="cursor-pointer font-medium text-indigo-700">{title}</summary>
        <pre className="mt-2 max-h-96 overflow-auto whitespace-pre-wrap break-words rounded bg-gray-50 p-3 text-xs">{JSON.stringify(value, null, 2)}</pre>
    </details>;
}

export default function OrderHistory({endpoint}) {
    const [page, setPage] = useState(1);
    const [type, setType] = useState('all');
    const [result, setResult] = useState(null);
    const [error, setError] = useState(false);
    const [loading, setLoading] = useState(true);
    const [refresh, setRefresh] = useState(0);
    useEffect(() => {
        const controller = new AbortController();
        setLoading(true);
        setError(false);
        axios.get(endpoint, {params: {page, type}, signal: controller.signal})
            .then(({data}) => setResult(data))
            .catch((error) => { if (!axios.isCancel(error)) setError(true); })
            .finally(() => { if (!controller.signal.aborted) setLoading(false); });
        return () => controller.abort();
    }, [endpoint, page, type, refresh]);

    return <section className="mt-6 rounded-lg bg-white p-6 shadow-sm" aria-label="Order history">
        <div className="flex flex-wrap items-center justify-between gap-3">
            <h2 className="text-lg font-semibold">History and errors</h2>
            <div className="flex gap-3">
                <select aria-label="Filter history" value={type} onChange={(event) => {setType(event.target.value); setPage(1);}} className="rounded border-gray-300">
                    <option value="all">All events</option><option value="changes">Changes</option><option value="errors">Errors</option>
                </select>
                <button type="button" onClick={() => setRefresh(refresh + 1)} className="text-indigo-700">Refresh</button>
            </div>
        </div>
        {loading ? <p role="status" className="py-4">Loading history…</p> : error ?
            <p role="alert" className="py-4 text-red-700">Could not load history. Try Refresh.</p> : <>
                {result?.data.length === 0 && <p className="py-4 text-gray-500">No recorded events. History is available for activity after auditing was enabled.</p>}
                <ol className="divide-y divide-gray-200">
                    {result?.data.map((entry) => <li key={entry.id} className="py-5">
                        <div className="flex flex-wrap justify-between gap-2">
                            <strong className={entry.event.includes('failed') ? 'text-red-700' : 'text-gray-900'}>{labels[entry.event] ?? entry.event}</strong>
                            <time className="text-sm text-gray-500" dateTime={entry.created_at}>{new Date(entry.created_at).toLocaleString()}</time>
                        </div>
                        <p className="mt-1 text-sm text-gray-600">
                            {entry.properties.actor?.name ?? 'Unknown'}{entry.properties.actor?.id ? ` · User #${entry.properties.actor.id}` : ''}
                            {entry.properties.recipient_type ? ` · ${entry.properties.recipient_type} email` : ''}
                            {entry.properties.entity_id ? ` · ${entry.properties.entity} #${entry.properties.entity_id}` : ''}
                        </p>
                        {entry.properties.exception && <p className="mt-2 break-words text-sm text-red-700">{entry.properties.exception}: {entry.properties.message}</p>}
                        {entry.properties.phase && <p className="text-sm">Step: {entry.properties.phase}</p>}
                        {Object.keys(entry.changes?.old ?? {}).length > 0 && <div className="mt-3 overflow-x-auto">
                            <table className="w-full text-left text-sm"><thead><tr><th>Field</th><th>Before</th><th>After</th></tr></thead><tbody>
                                {Object.entries(entry.changes.old).map(([key, value]) => <tr key={key}><td className="py-1 pr-3">{key}</td><td className="pr-3">{valueText(key, value)}</td><td>{valueText(key, entry.changes.attributes?.[key])}</td></tr>)}
                            </tbody></table>
                        </div>}
                        <JsonDetails title="Event details" value={{changes: entry.changes, ...entry.properties}}/>
                        <div className="mt-3 flex flex-wrap gap-4 text-sm text-indigo-700">
                            {entry.attempt_id && <a href={`/admin/order-attempts/${entry.attempt_id}`}>Attempt and payload</a>}
                            {entry.log_url && <a href={entry.log_url}>Open technical logs</a>}
                        </div>
                    </li>)}
                </ol>
                {result && <div className="mt-4 flex items-center justify-between text-sm">
                    <button disabled={page <= 1} onClick={() => setPage(page - 1)} className="text-indigo-700 disabled:text-gray-400">Previous</button>
                    <span>Page {result.current_page} / {result.last_page} · {result.total} events</span>
                    <button disabled={page >= result.last_page} onClick={() => setPage(page + 1)} className="text-indigo-700 disabled:text-gray-400">Next</button>
                </div>}
            </>}
    </section>;
}
