import AdminLayout from "@/Layouts/AdminLayout.jsx";
import {router, usePage} from "@inertiajs/react";
import {useState, useEffect, Fragment} from "react";
import {Listbox, Transition} from "@headlessui/react";
import SelectedItem from "@/Components/Core/Selectedttem.jsx";
import {CheckIcon, ChevronUpDownIcon} from "@heroicons/react/24/outline/index.js";
import UploadImagePlaceHolder from "@/Components/Core/UploadImagePlaceHolder";
import ImageHolder from "@/Components/Core/ImageHolder.jsx";
import ChoseMainImageModal from "@/Components/Modals/ChoseMainImageModal.jsx";
import ImageListItem from "@/Components/Core/ImageListItem.jsx";

const ProductCreate = () => {

    const { errors } = usePage().props;
    const [selectedCategories, setSelectedCategories] = useState([]);
    const [selectedModifierGroups, setSelectedModifierGroups] = useState([]);
    const [modifierGroups, setModifierGroups] = useState([]);
    const [productImages, setProductImages] = useState([]);
    const [categories, setCategories] = useState([]);
    const [mainImage, setMainImage] = useState(null);
    const [galleryImages, setGalleryImages] = useState([]);
    const [selectedImages, setSelectedImages] = useState([]);
    const [values, setValues] = useState({
        name: null,
        description: null,
        price: null,
        category_ids: [],
        modifier_group_ids: [],
        product_image_id: null,
        product_image_ids: []
    });

    const [choseMainImageModalOpen, setChoseMainImageModalOpen] = useState(false);
    const [choseGalleryImagesModalOpen, setChoseGalleryImagesModalOpen] = useState(false);

    useEffect(() => {
        // Function to fetch data from the API
        const fetchCategories = async () => {
            const response = await axios.get(route('admin.categories.all'));
            setCategories(response.data.json);
        };
        const fetchModifierGroups = async () => {
            const response = await axios.get(route('admin.modifier-groups.all'));
            setModifierGroups(response.data.json);
        }
        const fetchProductImages = async () => {
            const response = await axios.get(route('admin.images.all', 'product'));
            setProductImages(response.data.json);
        }
        // Call the fetchData functions on page load
        fetchCategories();
        fetchModifierGroups();
        fetchProductImages();
    }, []);

    function handleChange(e) {
        setValues(values => ({
            ...values,
            [e.target.id]: e.target.value,
        }))
    }

    function handleSubmit(e) {
        e.preventDefault();
        if (selectedCategories.length > 0) {
            values.category_ids = selectedCategories.map(item => item.id);
        }
        if (selectedModifierGroups.length > 0) {
            values.modifier_group_ids = selectedModifierGroups.map(item => item.id);
        }
        if (galleryImages.length > 0) {
            values.product_image_ids = galleryImages.map(obj => obj.id);
        }
        if (mainImage) {
            values.product_image_id = mainImage.id
        }

        router.post(route('admin.products.create'), values);
    }

    function classNames(...classes) {
        return classes.filter(Boolean).join(' ')
    }

    const addImageModalOpen = () => {
        setChoseMainImageModalOpen(true);
    }

    const addGalleryImagesOpenModal = () => {
        setChoseGalleryImagesModalOpen(true);
    }

    const handleOnImageClick = (id) => {
        setSelectedImages([id]);
        setMainImage(productImages.find(procductImage => procductImage.id === id));
        // selectedImages.includes(id) ? selectedImages(prev => prev.filter(el => el != id)) : setSelectedImages(prev => [...prev, id]); - example with multi
    }

    const handleOnGalleryImageClick = (id) => {
        const currentImage = productImages.find(productImage => productImage.id === id);
        galleryImages.includes(currentImage) ? setGalleryImages(prev => prev.filter(el => el.id !== currentImage.id)) : setGalleryImages(prev => [...prev, currentImage]);
    }

    return <AdminLayout>

        <ChoseMainImageModal images={productImages} setOpen={setChoseMainImageModalOpen} open={choseMainImageModalOpen} selectedImages={selectedImages} handleOnImageClick={handleOnImageClick}/>

        <ChoseMainImageModal images={productImages} setOpen={setChoseGalleryImagesModalOpen} open={choseGalleryImagesModalOpen} selectedImages={galleryImages.map(obj => obj.id)} handleOnImageClick={handleOnGalleryImageClick}/>

        <div className="space-y-10 divide-y divide-gray-900/10">
            <div className="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
                <div className="px-4 sm:px-0">
                    <h2 className="text-center text-base font-semibold leading-7 text-gray-900 mb-4">Chose Main Image</h2>

                    {mainImage
                        ? <ImageHolder image={mainImage} onClick={addImageModalOpen} className={'cursor-pointer'}/>
                        : <UploadImagePlaceHolder onClick={addImageModalOpen} className={'mt-16'} label={'Add Main Image.'}/>}
                    {errors.product_image_id && <div className={'text-red-500'}>{errors.product_image_id}</div>}

                    <h2 className="mt-16 text-center text-base font-semibold leading-7 text-gray-900 mb-4">Chose Gallery Images</h2>
                    {galleryImages.length > 0
                        ? <ul role="list" className="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 sm:gap-x-6 lg:grid-cols-4 xl:gap-x-8 mt-8 cursor-pointer" onClick={addGalleryImagesOpenModal}>
                            {galleryImages.map((file) => (
                                <div key={file.id}>
                                    <ImageListItem file={file} />
                                </div>
                            ))}
                        </ul> : <UploadImagePlaceHolder onClick={addGalleryImagesOpenModal} className={'mt-16'} label={'Add Gallery Images.'}/>}
                </div>

                <form onSubmit={handleSubmit} className="bg-white shadow-xs ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                    <div className="px-4 py-6 sm:p-8">
                        <div className="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <div className="sm:col-span-4">
                                <label htmlFor="name" className="block text-sm font-medium leading-6 text-gray-900">
                                    Product Name
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
                                    Product Slug
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

                            <div className="sm:col-span-4">
                                <label htmlFor="price" className="block text-sm font-medium leading-6 text-gray-900">
                                    Product Price
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

                            <div className="sm:col-span-4">
                                <Listbox value={selectedCategories} onChange={setSelectedCategories} multiple={true}>
                                    {({ open }) => (
                                        <>
                                            <Listbox.Label className="block text-sm font-medium leading-6 text-gray-900">Categories</Listbox.Label>
                                            <div className="relative min-h-24 mt-2">
                                                <Listbox.Button className="relative w-full cursor-default rounded-md bg-white py-1.5 pl-3 pr-10 text-left text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 focus:outline-hidden focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                                    {selectedCategories.length > 0 ? (
                                                        <span className="block truncate flex gap-2 flex-wrap">
                                                          {selectedCategories.map((el) => (
                                                              <SelectedItem item={el} key={el.id} />
                                                          ))}
                                                        </span>
                                                    ) : (
                                                        <span className="text-gray-400">Select categories...</span>
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
                                                        {categories.map((category) => (
                                                            <Listbox.Option
                                                                key={category.id}
                                                                className={({ active }) =>
                                                                    classNames(
                                                                        active ? 'bg-indigo-600 text-white' : 'text-gray-900',
                                                                        'relative cursor-default select-none py-2 pl-3 pr-9'
                                                                    )
                                                                }
                                                                value={category}
                                                            >
                                                                {({ selected, active }) => (
                                                                    <>
                                                            <span className={classNames(selected ? 'font-semibold' : 'font-normal', 'block truncate')}>
                                                              {category.name}
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

                            <div className="sm:col-span-4">
                                <Listbox value={selectedModifierGroups} onChange={setSelectedModifierGroups} multiple={true}>
                                    {({ open }) => (
                                        <>
                                            <Listbox.Label className="block text-sm font-medium leading-6 text-gray-900">Modifier Groups</Listbox.Label>
                                            <div className="relative min-h-24 mt-2">
                                                <Listbox.Button className="relative w-full cursor-default rounded-md bg-white py-1.5 pl-3 pr-10 text-left text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 focus:outline-hidden focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                                    {selectedModifierGroups.length > 0 ? (
                                                        <span className="block truncate flex gap-2 flex-wrap">
                                                          {selectedModifierGroups.map((el) => (
                                                              <SelectedItem item={el} key={el.id} />
                                                          ))}
                                                        </span>
                                                    ) : (
                                                        <span className="text-gray-400">Select Modifier Groups...</span>
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

                            <div className="col-span-full">
                                <label htmlFor="description" className="block text-sm font-medium leading-6 text-gray-900">
                                    Product Description
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
                                <p className="mt-3 text-sm leading-6 text-gray-600">Write a few sentences about a product.</p>
                            </div>

                        </div>
                    </div>
                    <div className="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                        <a
                            href={route('admin.products.index')}
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

export default ProductCreate;
