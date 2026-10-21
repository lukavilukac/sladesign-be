import AdminLayout from "@/Layouts/AdminLayout.jsx";
import axios from "axios";
import React, {useEffect, useState} from 'react';

const ModifierGroups = () => {

    const [modifierGroups, setModifierGroups] = useState([]);

    useEffect(() => {
        // Function to fetch data from the API
        const fetchModifierGroups = async () => {
            try {
                const response = await axios.get(route('admin.modifier-groups.all'));
                // const json = await response.json();
                setModifierGroups(response.data.json);
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        };

        // Call the fetchData function on page load
        fetchModifierGroups();
    }, []);

    return <AdminLayout>

        <div className="px-4 sm:px-6 lg:px-8">
            <div className="sm:flex sm:items-center">
                <div className="sm:flex-auto">
                    <h1 className="text-base font-semibold leading-6 text-gray-900">ModifierGroups</h1>
                    <p className="mt-2 text-sm text-gray-700">
                        A list of all the modifierGroups including their info.
                    </p>
                </div>
                <div className="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <a
                        href={route('admin.modifier-groups.new')}
                        type="button"
                        className="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    >
                        Add ModifierGroup
                    </a>
                </div>
            </div>
            <div className="mt-8 flow-root">
                <div className="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div className="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table className="min-w-full divide-y divide-gray-300">
                            <thead>
                            <tr>
                                <th scope="col" className="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                    Name
                                </th>
                                <th scope="col" className="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                    Is Required
                                </th>
                                <th scope="col" className="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                    Is Multi
                                </th>
                                <th scope="col" className="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                    <span className="sr-only">Edit</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                            {modifierGroups.map((modifierGroup) => (
                                <tr key={modifierGroup.id}>
                                    <td className="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                        <div className="flex items-center">
                                            <div className="h-11 w-11 shrink-0">
                                                {/*<img className="h-11 w-11 rounded-full" src={modifierGroup.image} alt="" />*/}
                                            </div>
                                            <div className="ml-4">
                                                <div className="font-medium text-gray-900">{modifierGroup.name}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                        <div className="text-gray-900">{modifierGroup.required ? 'Yes' : 'No'}</div>
                                    </td>
                                    <td className="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                        <div className="text-gray-900">{modifierGroup.is_multi ? 'Yes' : 'No'}</div>
                                    </td>
                                    <td className="relative whitespace-nowrap py-5 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                        <a href={route('admin.modifier-groups.show', modifierGroup.id)} className="text-indigo-600 hover:text-indigo-900">
                                            Edit<span className="sr-only">, {modifierGroup.name}</span>
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

export default ModifierGroups;
