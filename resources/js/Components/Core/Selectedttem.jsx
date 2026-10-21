import { XMarkIcon } from '@heroicons/react/20/solid'

const SelectedItem = ({item}) => {
    return <div className="bg-indigo-600 relative cursor-default select-none py-2 pl-3 pr-3 flex gap-2 items-center">
                <p className="text-sm  font-medium text-white">{item.name}</p>
                {/*<XMarkIcon className="h-3 w-3 fill-white" aria-hidden="true" />*/}
            </div>
}

export default SelectedItem
