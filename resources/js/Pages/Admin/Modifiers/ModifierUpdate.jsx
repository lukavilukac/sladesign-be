import AdminLayout from "@/Layouts/AdminLayout.jsx";
import {router, usePage} from "@inertiajs/react";
import {useState, useEffect, Fragment} from "react";
import {PuzzlePieceIcon, ChevronUpDownIcon, CheckIcon} from "@heroicons/react/24/outline";
import {Listbox, Transition} from "@headlessui/react";

const ModifierUpdate = ({modifier}) => {

    const { errors } = usePage().props;
    const [modifierGroups, setModifierGroups] = useState([]);
    const [selected, setSelected] = useState(null);

    const [values, setValues] = useState({
        value: modifier.value,
        en_value: modifier.en_value,
        description: modifier.description,
        en_description: modifier.en_description,
        price: modifier.price,
        modifier_group_id: modifier.modifier_group_id,
    });

    useEffect(() => {
        // Function to fetch data from the API
        const fetchModifierGroups = async () => {
            const response = await axios.get(route('admin.modifier-groups.all'));
            setModifierGroups(response.data.json);
            setSelected(response.data.json.find(obj => obj.id === modifier.modifier_group_id));
        };
        // Call the fetchData function on page load
        fetchModifierGroups();
    }, []);

    const handleSetSelected = (e) => {
        setSelected(e);
    }

    function handleChange(e) {
        setValues(values => ({
            ...values,
            [e.target.id]: e.target.value,
        }))
    }

    function handleSubmit(e) {
        e.preventDefault();
        values.modifier_group_id = selected.id;

        router.put(route('admin.modifiers.update', modifier.id), values);
    }

    function classNames(...classes) {
        return classes.filter(Boolean).join(' ')
    }

    return <AdminLayout>

        <div className="space-y-10 divide-y divide-gray-900/10">
            <div className="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
                <div className="px-4 sm:px-0">
                    <h2 className="text-base font-semibold leading-7 text-gray-900">Modifier Update</h2>
                    <p className="mt-1 text-sm leading-6 text-gray-600">
                        Here is the place where you can update a new Modifier.
                    </p>

                    <PuzzlePieceIcon className={'h10 fill-gray-300'} />
                </div>

                <form onSubmit={handleSubmit} className="bg-white shadow-xs ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                    <div className="px-4 py-6 sm:p-8">
                        <div className="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                            <div className="sm:col-span-4">
                                <label htmlFor="value" className="block text-sm font-medium leading-6 text-gray-900">
                                    Modifier Value
                                </label>
                                <div className="mt-2">
                                    <div className="flex rounded-md shadow-xs ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input
                                            type="text"
                                            name="value"
                                            id="value"
                                            className="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            placeholder="Some fancy value"
                                            value={values.value} onChange={handleChange}
                                        />
                                    </div>
                                    {errors.value && <div className={'text-red-500'}>{errors.value}</div>}
                                </div>
                            </div>

                        </div>

                    </div>

                    <div className="px-4 py-6 sm:p-8">
                        <div className="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                            <div className="sm:col-span-4">
                                <label htmlFor="value" className="block text-sm font-medium leading-6 text-gray-900">
                                    Modifier English Value
                                </label>
                                <div className="mt-2">
                                    <div className="flex rounded-md shadow-xs ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input
                                            type="text"
                                            name="en_value"
                                            id="en_value"
                                            className="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            placeholder="Some fancy value"
                                            value={values.en_value} onChange={handleChange}
                                        />
                                    </div>
                                    {errors.en_value && <div className={'text-red-500'}>{errors.en_value}</div>}
                                </div>
                            </div>

                        </div>

                    </div>

                    <div className="px-4 py-6 sm:p-8">
                        <div className="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                            <div className="sm:col-span-4">
                                <label htmlFor="description" className="block text-sm font-medium leading-6 text-gray-900">
                                    Modifier Description
                                </label>
                                <div className="mt-2">
                                    <div className="flex rounded-md shadow-xs ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input
                                            type="text"
                                            name="description"
                                            id="description"
                                            className="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            placeholder="Some fancy description"
                                            value={values.description} onChange={handleChange}
                                        />
                                    </div>
                                    {errors.description && <div className={'text-red-500'}>{errors.description}</div>}
                                </div>
                            </div>

                        </div>

                    </div>

                    <div className="px-4 py-6 sm:p-8">
                        <div className="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                            <div className="sm:col-span-4">
                                <label htmlFor="en_description" className="block text-sm font-medium leading-6 text-gray-900">
                                    Modifier English Description
                                </label>
                                <div className="mt-2">
                                    <div className="flex rounded-md shadow-xs ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input
                                            type="text"
                                            name="en_description"
                                            id="en_description"
                                            className="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            placeholder="Some fancy description"
                                            value={values.en_description} onChange={handleChange}
                                        />
                                    </div>
                                    {errors.en_description && <div className={'text-red-500'}>{errors.en_description}</div>}
                                </div>
                            </div>

                        </div>

                    </div>

                    <div className="px-4 py-6 sm:p-8">
                        <div className="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <div className="sm:col-span-4">
                                <label htmlFor="price" className="block text-sm font-medium leading-6 text-gray-900">
                                    Modifier Price
                                </label>
                                <div className="mt-2">
                                    <div className="flex rounded-md shadow-xs ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input
                                            type="number"
                                            name="price"
                                            id="price"
                                            className="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            placeholder="Some fancy price"
                                            value={values.price} onChange={handleChange}
                                        />
                                    </div>
                                </div>
                                {errors.price && <div className={'text-red-500'}>{errors.price}</div>}
                            </div>
                        </div>
                    </div>



                    <div className="px-4 py-6 sm:p-8">
                        <div className="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                            <div className="sm:col-span-4">
                                <Listbox value={selected} onChange={handleSetSelected}>
                                    {({ open }) => (
                                        <>
                                            <Listbox.Label className="block text-sm font-medium leading-6 text-gray-900">Modifier Group</Listbox.Label>
                                            <div className="relative mt-2">
                                                <Listbox.Button className="relative w-full cursor-default rounded-md bg-white py-1.5 pl-3 pr-10 text-left text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 focus:outline-hidden focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                                    <span className="block truncate">{selected && selected.name}</span>
                                                    <span className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                                        <ChevronUpDownIcon className="h-5 w-5 text-gray-400" aria-hidden="true" />
                                                    </span>
                                                </Listbox.Button>

                                                <Transition
                                                    show={open}
                                                    as={Fragment}
                                                    leave="transition ease-in duration-100"
                                                    leaveFrom="opacity-100"
                                                    leaveTo="opacity-0"
                                                >
                                                    <Listbox.Options className="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm">
                                                        {modifierGroups.map((modifierGroup) => (
                                                            <Listbox.Option
                                                                key={modifierGroup.id}
                                                                className={({ active }) =>
                                                                    classNames(
                                                                        active ? 'bg-indigo-600 text-white' : 'text-gray-900',
                                                                        'relative cursor-default select-none py-2 pl-3 pr-9'
                                                                    )
                                                                }
                                                                value={modifierGroup}
                                                            >
                                                                {({ selected, active }) => (
                                                                    <>
                                                                    <span className={classNames(selected ? 'font-semibold' : 'font-normal', 'block truncate')}>
                                                                      {modifierGroup.name}
                                                                    </span>

                                                                        {selected ? (
                                                                            <span
                                                                                className={classNames(
                                                                                    active ? 'text-white' : 'text-indigo-600',
                                                                                    'absolute inset-y-0 right-0 flex items-center pr-4'
                                                                                )}
                                                                            >
                                                                            <CheckIcon className="h-5 w-5" aria-hidden="true" />
                                                                          </span>
                                                                        ) : null}
                                                                    </>
                                                                )}
                                                            </Listbox.Option>
                                                        ))}
                                                    </Listbox.Options>
                                                </Transition>
                                            </div>
                                        </>
                                    )}
                                </Listbox>
                            </div>
                        </div>
                    </div>



                    <div className="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                        <a
                            href={route('admin.modifiers.index')}
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

export default ModifierUpdate;
