import { SwatchIcon } from '@heroicons/react/24/solid'
import AdminLayout from "@/Layouts/AdminLayout.jsx";
import {router, usePage} from "@inertiajs/react";
import {useState} from "react";
import {Switch} from "@headlessui/react";

const ModifierGroupCreate = () => {

    const { errors } = usePage().props;
    const [enabledRequired, setEnabledRequired] = useState(false);
    const [enabledIsMulti, setEnabledIsMulti] = useState(false);

    const [values, setValues] = useState({
        name: null,
        required: false,
        is_multi: false,
    });

    function handleChange(e) {
        setValues(values => ({
            ...values,
            [e.target.id]: e.target.value,
        }))
    }

    function handleSubmit(e) {
        e.preventDefault();
        values.required = enabledRequired;
        values.is_multi = enabledIsMulti;

        router.post(route('admin.modifier-groups.create'), values);
    }

    function classNames(...classes) {
        return classes.filter(Boolean).join(' ')
    }

    return <AdminLayout>

        <div className="space-y-10 divide-y divide-gray-900/10">
            <div className="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
                <div className="px-4 sm:px-0">
                    <h2 className="text-base font-semibold leading-7 text-gray-900">Create Modifier Group</h2>
                    <p className="mt-1 text-sm leading-6 text-gray-600">
                        Here is the place where you can create a new Modifier Group.
                    </p>

                    <SwatchIcon className={'h10 fill-gray-300'} />
                </div>

                <form onSubmit={handleSubmit} className="bg-white shadow-xs ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                    <div className="px-4 py-6 sm:p-8">
                        <div className="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                            <div className="sm:col-span-4">
                                <label htmlFor="name" className="block text-sm font-medium leading-6 text-gray-900">
                                    Modifier Group Name
                                </label>
                                <div className="mt-2">
                                    <div className="flex rounded-md shadow-xs ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input
                                            type="text"
                                            name="name"
                                            id="name"
                                            className="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            placeholder="Some fancy name"
                                            value={values.name} onChange={handleChange}
                                        />
                                    </div>
                                    {errors.name && <div className={'text-red-500'}>{errors.name}</div>}
                                </div>
                            </div>

                        </div>


                        <div className="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 mt-4">

                            <div className="sm:col-span-4">
                                <Switch.Group as="div" className="flex items-center">
                                    <Switch
                                        checked={enabledRequired}
                                        onChange={setEnabledRequired}
                                        className={classNames(
                                            enabledRequired ? 'bg-indigo-600' : 'bg-gray-200',
                                            'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2'
                                        )}
                                    >
                        <span
                            aria-hidden="true"
                            className={classNames(
                                enabledRequired ? 'translate-x-5' : 'translate-x-0',
                                'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out'
                            )}
                        />
                                    </Switch>
                                    <Switch.Label as="span" className="ml-3 text-sm">
                                        <span className="font-medium text-gray-900">Required</span>{' '}
                                        <span className="text-gray-500">This modifier group will be required for all the products that is attached to.</span>
                                    </Switch.Label>
                                </Switch.Group>
                            </div>
                        </div>

                        <div className="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 mt-4">

                            <div className="sm:col-span-4">
                                <Switch.Group as="div" className="flex items-center">
                                    <Switch
                                        checked={enabledIsMulti}
                                        onChange={setEnabledIsMulti}
                                        className={classNames(
                                            enabledIsMulti ? 'bg-indigo-600' : 'bg-gray-200',
                                            'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2'
                                        )}
                                    >
                        <span
                            aria-hidden="true"
                            className={classNames(
                                enabledIsMulti ? 'translate-x-5' : 'translate-x-0',
                                'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out'
                            )}
                        />
                                    </Switch>
                                    <Switch.Label as="span" className="ml-3 text-sm">
                                        <span className="font-medium text-gray-900">Is Multi</span>{' '}
                                        <span className="text-gray-500">It will be possible to chose multiple modifiers from this group.</span>
                                    </Switch.Label>
                                </Switch.Group>
                            </div>
                        </div>

                    </div>




                    <div className="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                        <a
                            href={route('admin.modifier-groups.index')}
                            type="button"
                            className="text-sm font-semibold leading-6 text-gray-900">
                            Cancel
                        </a>
                        <button
                            type="submit"
                            className="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            Save
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </AdminLayout>
}

export default ModifierGroupCreate;
