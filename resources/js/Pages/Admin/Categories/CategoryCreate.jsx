import { ServerStackIcon } from '@heroicons/react/24/solid'
import AdminLayout from "@/Layouts/AdminLayout.jsx";
import {router, usePage} from "@inertiajs/react";
import {useEffect, useState} from "react";
import ChoseMainImageModal from "@/Components/Modals/ChoseMainImageModal.jsx";
import ImageHolder from "@/Components/Core/ImageHolder.jsx";
import UploadImagePlaceHolder from "@/Components/Core/UploadImagePlaceHolder.jsx";


const CategoryCreate = () => {

    const { errors } = usePage().props;
    const [categoryImages, setCategoryImages] = useState([]);
    const [choseMainImageModalOpen, setChoseMainImageModalOpen] = useState(false);
    const [mainImage, setMainImage] = useState(null);
    const [selectedImages, setSelectedImages] = useState([]);
    const [values, setValues] = useState({
        name: null,
        description: null,
        email: null,
        category_image_id: null
    });

    function handleChange(e) {
        setValues(values => ({
            ...values,
            [e.target.id]: e.target.value,
        }))
    }

    function handleSubmit(e) {
        e.preventDefault();
        if (mainImage) {
            values.category_image_id = mainImage.id
        }
        router.post(route('admin.categories.create'), values);
    }

    const fetchCategoryImages = async () => {
        const response = await axios.get(route('admin.images.all', 'category'));
        setCategoryImages(response.data.json);
    }

    const addImageModalOpen = () => {
        setChoseMainImageModalOpen(true);
    }

    const handleOnImageClick = (id) => {
        setSelectedImages([id]);
        setMainImage(categoryImages.find(categoryImage => categoryImage.id === id));
        // selectedImages.includes(id) ? selectedImages(prev => prev.filter(el => el != id)) : setSelectedImages(prev => [...prev, id]); - example with multi
    }

    useEffect(() => {
        fetchCategoryImages();
    }, []);

    return <AdminLayout>

        <ChoseMainImageModal images={categoryImages} setOpen={setChoseMainImageModalOpen} open={choseMainImageModalOpen} selectedImages={selectedImages} handleOnImageClick={handleOnImageClick}/>

        <div className="space-y-10 divide-y divide-gray-900/10">
            <div className="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
                <div className="px-4 sm:px-0">
                    <h2 className="text-center text-base font-semibold leading-7 text-gray-900 mb-4">Chose Main Image</h2>

                    {mainImage
                        ? <ImageHolder image={mainImage} onClick={addImageModalOpen} className={'cursor-pointer'}/>
                        : <UploadImagePlaceHolder onClick={addImageModalOpen} className={'mt-16'} label={'Add Main Image.'}/>}
                    {errors.category_image_id && <div className={'text-red-500'}>{errors.category_image_id}</div>}
                </div>

                <form onSubmit={handleSubmit} className="bg-white shadow-xs ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                    <div className="px-4 py-6 sm:p-8">
                        <div className="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <div className="sm:col-span-4">
                                <label htmlFor="name" className="block text-sm font-medium leading-6 text-gray-900">
                                    Category Name
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

                            <div className="sm:col-span-4">
                                <label htmlFor="website" className="block text-sm font-medium leading-6 text-gray-900">
                                    Category Slug
                                </label>
                                <div className="mt-2">
                                    <div className="flex rounded-md shadow-xs ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input
                                            type="text"
                                            name="slug"
                                            id="slug"
                                            className="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            placeholder="Some fancy slug"
                                            value={values.slug} onChange={handleChange}
                                        />
                                    </div>
                                    {errors.slug && <div className={'text-red-500'}>{errors.slug}</div>}
                                </div>
                            </div>

                            <div className="col-span-full">
                                <label htmlFor="description" className="block text-sm font-medium leading-6 text-gray-900">
                                    Category Description
                                </label>
                                <div className="mt-2">
                  <textarea
                      id="description"
                      name="description"
                      rows={3}
                      className="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                      defaultValue={''}
                      value={values.description}
                      onChange={handleChange}
                  />
                                </div>
                                {errors.description && <div className={'text-red-500'}>{errors.description}</div>}
                                <p className="mt-3 text-sm leading-6 text-gray-600">Write a few sentences about a category.</p>
                            </div>

                        </div>
                    </div>
                    <div className="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                        <a
                            href={route('admin.categories.index')}
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

export default CategoryCreate;
