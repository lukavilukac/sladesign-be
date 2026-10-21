import {Listbox, Transition} from "@headlessui/react";
import SelectedItem from "@/Components/Core/Selectedttem.jsx";
import {CheckIcon, ChevronUpDownIcon} from "@heroicons/react/24/outline/index.js";
import {Fragment} from "react";

const MultiSelect = ({items, selectedItems, handleSetSelectedItems, label, placeHolder}) => {

    const classNames = (...classes) => {
        return classes.filter(Boolean).join(' ')
    }

    return <div className="sm:col-span-4">
        <Listbox value={selectedItems} onChange={handleSetSelectedItems} multiple={true}>
            {({ open }) => (
                <>
                    <Listbox.Label className="block text-sm font-medium leading-6 text-gray-900">{label}</Listbox.Label>
                    <div className="relative min-h-24 mt-2">
                        <Listbox.Button className="relative w-full cursor-default rounded-md bg-white py-1.5 pl-3 pr-10 text-left text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 focus:outline-hidden focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            {selectedItems.length > 0 ? (
                                <span className="block truncate flex gap-2 flex-wrap">
                                  {selectedItems.map((el) => (
                                      <SelectedItem item={el} key={el.id} />
                                  ))}
                                </span>
                            ) : (
                                <span className="text-gray-400">{placeHolder}...</span>
                            )}
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
                                {items.map((item) => (
                                    <Listbox.Option
                                        key={item.id}
                                        className={({ active }) =>
                                            classNames(
                                                active ? 'bg-indigo-600 text-white' : 'text-gray-900',
                                                'relative cursor-default select-none py-2 pl-3 pr-9'
                                            )
                                        }
                                        value={item}
                                    >
                                        {({  active }) => (
                                            <>
                                            <span className={classNames(selectedItems.some((_item) => _item.id === item.id) ? 'font-semibold' : 'font-normal', 'block truncate')}>
                                              {item.name}
                                            </span>

                                                {selectedItems.some((_item) => _item.id === item.id) ? (
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
}

export default MultiSelect;
